<?php

namespace App\Services;

use App\Models\Transfert;
use App\Models\Transaction;
use App\Models\Utilisateur;
use App\Models\Portefeuille;
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
        $destinataire = Utilisateur::where('numero_telephone', $data['telephoneDestinataire'])->first();

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

        $idTransfert = 'trf_' . Str::random(10);

        $transfert = Transfert::create([
            'idTransfert' => $idTransfert,
            'idUtilisateur' => $utilisateur->id,
            'telephoneDestinataire' => $data['telephoneDestinataire'],
            'montant' => $data['montant'],
            'devise' => $data['devise'] ?? 'XOF',
            'frais' => $frais,
            'note' => $data['note'] ?? null,
            'statut' => 'en_attente_confirmation',
            'dateExpiration' => Carbon::now()->addMinutes(5),
        ]);

        return [
            'success' => true,
            'data' => [
                'idTransfert' => $transfert->idTransfert,
                'statut' => $transfert->statut,
                'montant' => $transfert->montant,
                'frais' => $transfert->frais,
                'montantTotal' => $transfert->montant + $transfert->frais,
                'destinataire' => [
                    'numeroTelephone' => $destinataire->numero_telephone,
                    'nom' => $destinataire->prenom . ' ' . $destinataire->nom,
                ],
                'dateExpiration' => $transfert->dateExpiration->toISOString(),
            ],
            'message' => 'Veuillez confirmer le transfert avec votre code PIN'
        ];
    }

    // 3.3 Confirmer un Transfert
    public function confirmerTransfert($utilisateur, $idTransfert, $codePin)
    {
        $transfert = Transfert::where('idTransfert', $idTransfert)
                              ->where('idUtilisateur', $utilisateur->id)
                              ->where('statut', 'en_attente_confirmation')
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

        if (Carbon::now()->isAfter($transfert->dateExpiration)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_004',
                    'message' => 'Transfert expiré'
                ],
                'status' => 422
            ];
        }

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

        $result = DB::transaction(function () use ($transfert, $utilisateur) {
            $portefeuilleExpediteur = $utilisateur->portefeuille;
            $destinataire = Utilisateur::where('numero_telephone', $transfert->telephoneDestinataire)->first();
            $portefeuilleDestinataire = $destinataire->portefeuille;

            // Débiter l'expéditeur
            $portefeuilleExpediteur->decrement('solde', $transfert->montant + $transfert->frais);

            // Créditer le destinataire
            $portefeuilleDestinataire->increment('solde', $transfert->montant);

            // Créer la transaction
            $idTransaction = 'txn_' . Str::random(10);
            Transaction::create([
                'idTransaction' => $idTransaction,
                'idUtilisateur' => $utilisateur->id,
                'type' => 'transfert',
                'montant' => $transfert->montant,
                'devise' => $transfert->devise,
                'numeroTelephoneDestinataire' => $transfert->telephoneDestinataire,
                'nomDestinataire' => $destinataire->prenom . ' ' . $destinataire->nom,
                'statut' => 'termine',
                'dateTransaction' => Carbon::now(),
                'reference' => 'OM' . date('YmdHis') . rand(100000, 999999),
                'frais' => $transfert->frais,
                'note' => $transfert->note,
            ]);

            // Mettre à jour le transfert
            $transfert->update([
                'statut' => 'termine',
                'idTransaction' => $idTransaction,
            ]);

            return [
                'idTransaction' => $idTransaction,
                'reference' => 'OM' . date('YmdHis') . rand(100000, 999999),
            ];
        });

        return [
            'success' => true,
            'data' => [
                'idTransaction' => $result['idTransaction'],
                'statut' => 'termine',
                'montant' => $transfert->montant,
                'destinataire' => [
                    'numeroTelephone' => $transfert->telephoneDestinataire,
                    'nom' => $transfert->nomDestinataire ?? 'Destinataire',
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
        $transfert = Transfert::where('idTransfert', $idTransfert)
                              ->where('idUtilisateur', $utilisateur->id)
                              ->where('statut', 'en_attente_confirmation')
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

        if (Carbon::now()->isAfter($transfert->dateExpiration)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'TRANSFER_004',
                    'message' => 'Transfert expiré'
                ],
                'status' => 422
            ];
        }

        $transfert->update(['statut' => 'annule']);

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