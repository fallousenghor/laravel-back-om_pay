<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\Transaction;
use App\Models\Marchand;
use App\Models\QRCode;
use App\Models\CodePaiement;
use App\Models\OrangeMoney;
use App\Interfaces\PaiementServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaiementService implements PaiementServiceInterface
{
    // 4.1 Lister les Catégories de Marchands
    public function listerCategories()
    {
       
        $categories = Marchand::all()->groupBy('categorie')->map(function ($items, $categorie) {
            return [
                'idCategorie' => 'cat_' . strtolower(str_replace(' ', '_', $categorie)),
                'nom' => $categorie,
                'description' => 'Description de ' . $categorie,
                'icone' => strtolower(str_replace(' ', '_', $categorie)),
                'nombreMarchands' => count($items),
            ];
        })->values();

        return [
            'success' => true,
            'data' => [
                'categories' => $categories
            ]
        ];
    }

    // 4.2 Scanner un QR Code
    public function scannerQR($donneesQR)
    {
        // Parser les données QR (format: OM_PAY_{idMarchand}_{montant}_{timestamp}_{signature})
        $parts = explode('_', $donneesQR);
        if (count($parts) !== 6 || $parts[0] !== 'OM' || $parts[1] !== 'PAY') {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_003',
                    'message' => 'QR Code invalide'
                ],
                'status' => 422
            ];
        }

        $idMarchand = $parts[2];
        $montant = (int) $parts[3];
        $timestamp = $parts[4];
        $signature = $parts[5];

        $marchand = Marchand::find($idMarchand);
        if (!$marchand) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_001',
                    'message' => 'Marchand introuvable'
                ],
                'status' => 404
            ];
        }

        // Vérifier si le QR n'est pas expiré (5 minutes)
        $qrTime = Carbon::createFromTimestamp($timestamp);
        if (Carbon::now()->diffInMinutes($qrTime) > 5) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_004',
                    'message' => 'QR Code expiré'
                ],
                'status' => 422
            ];
        }

        $idScan = 'scn_' . Str::random(10);

        return [
            'success' => true,
            'data' => [
                'idScan' => $idScan,
                'marchand' => [
                    'idMarchand' => $marchand->id,
                    'nom' => $marchand->nom,
                    'logo' => $marchand->logo,
                ],
                'montant' => $montant,
                'devise' => 'XOF',
                'dateExpiration' => $qrTime->addMinutes(5)->toIso8601String(),
                'valide' => true,
            ],
            'message' => 'QR Code scanné avec succès'
        ];
    }

    // 4.3 Saisir un Code de Paiement
    public function saisirCode($code)
    {
        // The merchant code can be stored directly on the OrangeMoney accounts (nullable)
        $compte = OrangeMoney::where('code', $code)
                              ->where('status', 'active')
                              ->first();

        if (!$compte) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_006',
                    'message' => 'Code de paiement invalide'
                ],
                'status' => 422
            ];
        }

        $idCode = 'cod_' . Str::random(10);

        return [
            'success' => true,
            'data' => [
                'idCode' => $idCode,
                'marchand' => [
                    // OrangeMoney entry acts as merchant account for the code
                    'idMarchand' => $compte->id,
                    'nom' => ($compte->prenom ?? '') . ' ' . ($compte->nom ?? ''),
                    'logo' => null,
                ],
                // No fixed amount for merchant code stored on OrangeMoney by default
                'montant' => null,
                'devise' => 'XOF',
                'dateExpiration' => null,
                'valide' => true,
            ],
            'message' => 'Code validé avec succès'
        ];
    }

    // 4.4 Initier un Paiement
    public function initierPaiement($utilisateur, $data)
    {
        $marchand = Marchand::find($data['idMarchand']);

        if (!$marchand) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_001',
                    'message' => 'Marchand introuvable'
                ],
                'status' => 404
            ];
        }

        $portefeuille = $utilisateur->portefeuille;

        if ($portefeuille->solde < $data['montant']) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'WALLET_001',
                    'message' => 'Solde insuffisant'
                ],
                'status' => 422
            ];
        }

        // Create Transaction first
        $transaction = new Transaction();
        $transaction->id_utilisateur = $utilisateur->id;
        $transaction->type = 'paiement';
        $transaction->montant = $data['montant'];
        $transaction->devise = 'XOF';
        $transaction->nom_marchand = $marchand->nom;
        $transaction->categorie_marchand = $marchand->categorie;
        $transaction->initier(); // Generates reference and sets statut='en_attente'

        // Create optional Paiement metadata record
        $paiement = new Paiement();
        $paiement->id = $transaction->id;
        $paiement->id_marchand = $data['idMarchand'];
        $paiement->mode_paiement = $data['modePaiement'] ?? 'qr_code';
        $paiement->details_paiement = $data['detailsPaiement'] ?? null;
        $paiement->save();

        return [
            'success' => true,
            'data' => [
                'idPaiement' => $transaction->id,
                'statut' => $transaction->statut,
                'marchand' => [
                    'idMarchand' => $marchand->id,
                    'nom' => $marchand->nom,
                    'logo' => $marchand->logo,
                ],
                'montant' => $transaction->montant,
                'frais' => 0, // Frais à la charge du marchand
                'montantTotal' => $transaction->montant,
                'dateExpiration' => $transaction->created_at->addMinutes(5)->toIso8601String(),
            ],
            'message' => 'Veuillez confirmer le paiement avec votre code PIN'
        ];
    }

    // 4.5 Confirmer un Paiement
    public function confirmerPaiement($utilisateur, $idPaiement, $codePin)
    {
        // Le paramètre idPaiement peut être soit un UUID (id), soit une référence
        $transaction = Transaction::where('reference', $idPaiement)
                                  ->where('id_utilisateur', $utilisateur->id)
                                  ->where('type', 'paiement')
                                  ->where('statut', 'en_attente')
                                  ->first();

        // Si pas trouvé par référence, essayer par id
        if (!$transaction) {
            $transaction = Transaction::where('id', $idPaiement)
                                      ->where('id_utilisateur', $utilisateur->id)
                                      ->where('type', 'paiement')
                                      ->where('statut', 'en_attente')
                                      ->first();
        }

        if (!$transaction) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_010',
                    'message' => 'Paiement introuvable ou déjà confirmé'
                ],
                'status' => 404
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

        // Check wallet balance
        $portefeuille = $utilisateur->portefeuille;
        if ($portefeuille->solde < $transaction->montant) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'WALLET_001',
                    'message' => 'Solde insuffisant'
                ],
                'status' => 422
            ];
        }

        $result = DB::transaction(function () use ($transaction, $utilisateur, $portefeuille) {
            // Transition states: en_attente -> en_cours -> termine
            if (!$transaction->valider()) {
                throw new \Exception('Cannot transition to en_cours state');
            }

            // Debit user wallet
            $portefeuille->decrement('solde', $transaction->montant);

            // Execute payment
            if (!$transaction->executer()) {
                throw new \Exception('Cannot execute payment');
            }

            return [
                'idTransaction' => $transaction->id,
                'reference' => $transaction->reference,
            ];
        });

        return [
            'success' => true,
            'data' => [
                'idTransaction' => $result['idTransaction'],
                'statut' => 'termine',
                'marchand' => [
                    'nom' => $transaction->nom_marchand,
                    'categorie' => $transaction->categorie_marchand,
                ],
                'montant' => $transaction->montant,
                'dateTransaction' => $transaction->date_transaction->toIso8601String(),
                'reference' => $result['reference'],
                'recu' => 'https://cdn.ompay.sn/recus/' . $result['idTransaction'] . '.pdf',
            ],
            'message' => 'Paiement effectué avec succès'
        ];
    }

    // 4.6 Annuler un Paiement
    public function annulerPaiement($utilisateur, $idPaiement)
    {
        // Le paramètre idPaiement peut être soit un UUID (id), soit une référence
        $paiement = Paiement::where('reference', $idPaiement)
                            ->where('id_utilisateur', $utilisateur->id)
                            ->where('type', 'paiement')
                            ->where('statut', 'en_attente')
                            ->first();

        // Si pas trouvé par référence, essayer par id
        if (!$paiement) {
            $paiement = Paiement::where('id', $idPaiement)
                                ->where('id_utilisateur', $utilisateur->id)
                                ->where('type', 'paiement')
                                ->where('statut', 'en_attente')
                                ->first();
        }

        if (!$paiement) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_010',
                    'message' => 'Paiement introuvable ou déjà annulé'
                ],
                'status' => 404
            ];
        }

        // Les paiements n'ont pas de date d'expiration, on procède directement à l'annulation
        // Transition de l'état via la transaction
        if (!$paiement->annuler()) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_011',
                    'message' => 'Impossible d\'annuler ce paiement'
                ],
                'status' => 422
            ];
        }

        return [
            'success' => true,
            'message' => 'Paiement annulé avec succès',
            'data' => [
                'idPaiement' => $paiement->id,
                'statut' => 'ANNULE',
                'dateAnnulation' => $paiement->updated_at->toIso8601String(),
            ]
        ];
    }
}