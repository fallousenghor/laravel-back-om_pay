<?php

namespace App\Services;

use App\Models\Transfert;
use App\Models\Transaction;
use App\Models\Utilisateur;
use App\Models\Portefeuille;
use App\Models\OrangeMoney;
use App\Models\Destinataire;
use App\Interfaces\TransfertServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransfertService implements TransfertServiceInterface
{
    // 3.1 Vérifier un Destinataire
    public function verifierDestinataire($numeroTelephone)
    {
        $destinataire = Utilisateur::where('numero_telephone', $numeroTelephone)->first();

        if (!$destinataire) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_001',
                    'message' => 'Destinataire introuvable'
                ],
                'status' => 404
            ];
        }

        return [
            'success' => true,
            'data' => [
                'estValide' => true,
                'nom' => $destinataire->prenom . ' ' . $destinataire->nom,
                'numeroTelephone' => $destinataire->numero_telephone,
                'operateur' => 'Orange', // Simulé
            ]
        ];
    }

    // 3.2 Initier un Transfert
    public function initierTransfert($utilisateur, $data)
    {
        // Vérifier d'abord si le destinataire a un compte Orange Money
        $compte_om_destinataire = OrangeMoney::where('numero_telephone', $data['telephoneDestinataire'])->first();

        if (!$compte_om_destinataire) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_001',
                    'message' => 'Le destinataire n\'a pas de compte Orange Money'
                ],
                'status' => 404
            ];
        }

        // Vérifier si le destinataire a un compte OM Pay (le créer automatiquement si nécessaire)
        $destinataire = Utilisateur::where('numero_telephone', $data['telephoneDestinataire'])->first();

        if (!$destinataire) {
            // Créer automatiquement le compte OM Pay du destinataire
            $destinataire = Utilisateur::create([
                'numero_telephone' => $data['telephoneDestinataire'],
                'prenom' => $compte_om_destinataire->prenom,
                'nom' => $compte_om_destinataire->nom,
                'email' => null,
                'code_pin' => null, // Sera défini lors de la première connexion du destinataire
                'numero_cni' => $compte_om_destinataire->numero_cni,
                'statut_kyc' => 'verifie'
            ]);

            // Créer le portefeuille du destinataire
            Portefeuille::create([
                'id_utilisateur' => $destinataire->id,
                'solde' => $compte_om_destinataire->solde,
                'devise' => 'XOF',
            ]);
        }

        if ($destinataire->id === $utilisateur->id) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_003',
                    'message' => 'Transfert à soi-même interdit'
                ],
                'status' => 422
            ];
        }

        $portefeuille = $utilisateur->portefeuille;
        $frais = $this->calculerFrais($data['montant']);

        if (!$portefeuille || $portefeuille->solde < ($data['montant'] + $frais)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'WALLET_001',
                    'message' => 'Solde insuffisant'
                ],
                'status' => 422
            ];
        }

        // Create the underlying transaction first (snake_case column names)
        // Create Transaction instance and initialize it (generates reference before saving)
        $transaction = new Transaction([
            'id_utilisateur' => $utilisateur->id,
            'type' => 'transfert',
            'montant' => $data['montant'],
            'devise' => $data['devise'] ?? 'XOF',
            'frais' => $frais,
            'numero_telephone_destinataire' => $data['telephoneDestinataire'],
            'nom_destinataire' => $destinataire->prenom . ' ' . $destinataire->nom,
            'note' => $data['note'] ?? null,
        ]);

        // initier() génère la référence et met le statut avant de sauver
        if (method_exists($transaction, 'initier')) {
            $transaction->initier();
        } else {
            // fallback: ensure reference exists then save
            if (empty($transaction->reference) && method_exists($transaction, 'genererReference')) {
                $transaction->reference = $transaction->genererReference();
            }
            $transaction->statut = $transaction->statut ?? 'en_attente';
            $transaction->save();
        }

        // Ensure a destinataire record exists in destinataires table
        $dest = Destinataire::firstOrCreate(
            ['numero_telephone' => $data['telephoneDestinataire']],
            ['nom' => $destinataire->prenom . ' ' . $destinataire->nom, 'operateur' => 'orange', 'est_valide' => true]
        );

        // Create the transfert record referencing the transaction
        // Depending on the migration used, the transferts table may have either
        // an `id_destinataire` FK or a `numero_telephone_destinataire` column.
        // Use the phone column which is present in the later migration.
        $transfertData = [
            'id_transaction' => $transaction->id,
            'id_expediteur' => $utilisateur->id,
            'nom_destinataire' => $dest->nom,
            'note' => $data['note'] ?? null,
            'date_expiration' => Carbon::now()->addMinutes(5),
        ];

        // prefer phone column
        $transfertData['numero_telephone_destinataire'] = $data['telephoneDestinataire'];

        // create transfert
        $transfert = Transfert::create($transfertData);

        return [
            'success' => true,
            'data' => [
                'idTransfert' => $transfert->id,
                'statut' => $transaction->statut,
                'montant' => $transaction->montant,
                'frais' => $transaction->frais,
                'montantTotal' => $transaction->montant + $transaction->frais,
                'destinataire' => [
                    'numeroTelephone' => $dest->numero_telephone,
                    'nom' => $dest->nom,
                ],
                'dateExpiration' => $transfert->date_expiration ? $transfert->date_expiration->toISOString() : null,
            ],
            'message' => 'Veuillez confirmer le transfert avec votre code PIN'
        ];
    }

    // 3.3 Confirmer un Transfert
    public function confirmerTransfert($utilisateur, $idTransfert, $codePin)
    {
        // Le paramètre idTransfert peut être soit un UUID (id du transfert), soit une référence (reference de la transaction)
        // On essaie d'abord avec la référence, puis avec l'id du transfert
        $transaction = Transaction::where('reference', $idTransfert)
                                  ->where('id_utilisateur', $utilisateur->id)
                                  ->where('type', 'transfert')
                                  ->where('statut', 'en_attente')
                                  ->first();

        // Si pas trouvé par référence, essayer par id_transaction du transfert
        if (!$transaction) {
            $transfert = Transfert::where('id', $idTransfert)
                                  ->where('id_expediteur', $utilisateur->id)
                                  ->first();
            
            if (!$transfert) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'TRANSFER_005',
                        'message' => 'Transfert introuvable ou déjà confirmé'
                    ],
                    'status' => 404
                ];
            }
            
            $transaction = Transaction::find($transfert->id_transaction);
        } else {
            // Récupérer le transfert associé
            $transfert = Transfert::where('id_transaction', $transaction->id)->first();
        }

        if (!$transaction || !$transfert) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_005',
                    'message' => 'Transfert introuvable ou déjà confirmé'
                ],
                'status' => 404
            ];
        }

        if ($transaction->statut !== 'en_attente') {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_005',
                    'message' => 'Transfert introuvable ou déjà traité'
                ],
                'status' => 404
            ];
        }

        // Check if transfer has expired
        if ($transfert->date_expiration && $transfert->date_expiration->isPast()) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_004',
                    'message' => 'Transfert expiré'
                ],
                'status' => 422
            ];
        }

        // Validate PIN
        if (!Hash::check($codePin, $utilisateur->code_pin)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'USER_006',
                    'message' => 'Code PIN incorrect'
                ],
                'status' => 401
            ];
        }

        $result = DB::transaction(function () use ($transfert, $utilisateur, $transaction) {
            $portefeuilleExpediteur = $utilisateur->portefeuille;
            $destinataireUser = Utilisateur::where('numero_telephone', $transaction->numero_telephone_destinataire)->first();
            $portefeuilleDestinataire = $destinataireUser->portefeuille;

            // Débiter l'expéditeur
            $portefeuilleExpediteur->decrement('solde', $transaction->montant + $transaction->frais);

            // Créditer le destinataire
            $portefeuilleDestinataire->increment('solde', $transaction->montant);

            // Progress transaction statut
            if (method_exists($transaction, 'valider')) {
                $transaction->valider();
            }
            if (method_exists($transaction, 'executer')) {
                $transaction->executer();
            }

            // return identifiers
            return [
                'idTransaction' => $transaction->id,
                'reference' => $transaction->reference ?? ('OM' . date('YmdHis') . rand(100000, 999999)),
            ];
        });

        return [
            'success' => true,
            'data' => [
                'idTransaction' => $result['idTransaction'],
                'statut' => 'termine',
                'montant' => $transaction->montant,
                'destinataire' => [
                    'numeroTelephone' => $transaction->numero_telephone_destinataire,
                    'nom' => $transaction->nom_destinataire ?? 'Destinataire',
                ],
                'dateTransaction' => Carbon::now()->toISOString(),
                'reference' => $result['reference'],
                'recu' => 'https://cdn.ompay.sn/recus/' . $result['idTransaction'] . '.pdf',
            ],
            'message' => 'Transfert effectué avec succès'
        ];
    }

    // 3.4 Annuler un Transfert
    public function annulerTransfert($utilisateur, $idTransfert)
    {
        // Le paramètre idTransfert peut être soit un UUID (id du transfert), soit une référence (reference de la transaction)
        // On essaie d'abord avec la référence, puis avec l'id du transfert
        $transaction = Transaction::where('reference', $idTransfert)
                                  ->where('id_utilisateur', $utilisateur->id)
                                  ->where('type', 'transfert')
                                  ->first();

        // Si pas trouvé par référence, essayer par id du transfert
        if (!$transaction) {
            $transfert = Transfert::where('id', $idTransfert)
                                  ->where('id_expediteur', $utilisateur->id)
                                  ->first();
            
            if (!$transfert) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'TRANSFER_006',
                        'message' => 'Transfert introuvable ou déjà annulé'
                    ],
                    'status' => 404
                ];
            }
            
            $transaction = Transaction::find($transfert->id_transaction);
        } else {
            // Récupérer le transfert associé
            $transfert = Transfert::where('id_transaction', $transaction->id)->first();
        }

        if (!$transaction || !$transfert) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_006',
                    'message' => 'Transfert introuvable ou déjà annulé'
                ],
                'status' => 404
            ];
        }

        // Check if transfer has expired
        if ($transfert->date_expiration && $transfert->date_expiration->isPast()) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_004',
                    'message' => 'Transfert expiré'
                ],
                'status' => 422
            ];
        }

        // Cancel the underlying transaction
        if (!$transaction->annuler()) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_007',
                    'message' => 'Impossible d\'annuler ce transfert'
                ],
                'status' => 422
            ];
        }

        return [
            'success' => true,
            'message' => 'Transfert annulé avec succès'
        ];
    }

    private function calculerFrais($montant)
    {
        if ($montant <= 5000) return 0;
        if ($montant <= 25000) return 0;
        if ($montant <= 50000) return 0;
        if ($montant <= 100000) return 100;
        return 200;
    }
}