<?php

namespace App\Services;

use App\Models\Paiement;
use App\Models\Transaction;
use App\Models\Marchand;
use App\Models\QRCode;
use App\Models\CodePaiement;
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
                    'idMarchand' => $marchand->idMarchand,
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
        $codePaiement = CodePaiement::where('code', $code)
                                    ->where('statut', 'actif')
                                    ->where('dateExpiration', '>', Carbon::now())
                                    ->first();

        if (!$codePaiement) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_006',
                    'message' => 'Code de paiement invalide'
                ],
                'status' => 422
            ];
        }

        $marchand = $codePaiement->marchand;
        $idCode = 'cod_' . Str::random(10);

        return [
            'success' => true,
            'data' => [
                'idCode' => $idCode,
                'marchand' => [
                    'idMarchand' => $marchand->idMarchand,
                    'nom' => $marchand->nom,
                    'logo' => $marchand->logo,
                ],
                'montant' => $codePaiement->montant,
                'devise' => $codePaiement->devise,
                'dateExpiration' => optional($codePaiement->dateExpiration)?->toIso8601String(),
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

        $idPaiement = 'pay_' . Str::random(10);

        $paiement = Paiement::create([
            'idPaiement' => $idPaiement,
            'idUtilisateur' => $utilisateur->id,
            'idMarchand' => $data['idMarchand'],
            'montant' => $data['montant'],
            'devise' => 'XOF',
            'modePaiement' => $data['modePaiement'],
            'statut' => 'en_attente_confirmation',
            'dateExpiration' => Carbon::now()->addMinutes(5),
        ]);

        return [
            'success' => true,
            'data' => [
                'idPaiement' => $paiement->idPaiement,
                'statut' => $paiement->statut,
                'marchand' => [
                    'idMarchand' => $marchand->idMarchand,
                    'nom' => $marchand->nom,
                    'logo' => $marchand->logo,
                ],
                'montant' => $paiement->montant,
                'frais' => 0, // Frais à la charge du marchand
                'montantTotal' => $paiement->montant,
                'dateExpiration' => optional($paiement->dateExpiration)?->toIso8601String(),
            ],
            'message' => 'Veuillez confirmer le paiement avec votre code PIN'
        ];
    }

    // 4.5 Confirmer un Paiement
    public function confirmerPaiement($utilisateur, $idPaiement, $codePin)
    {
        $paiement = Paiement::where('idPaiement', $idPaiement)
                            ->where('idUtilisateur', $utilisateur->id)
                            ->where('statut', 'en_attente_confirmation')
                            ->first();

        if (!$paiement) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_010',
                    'message' => 'Paiement introuvable ou déjà confirmé'
                ],
                'status' => 404
            ];
        }

        if (Carbon::now()->isAfter($paiement->dateExpiration)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_009',
                    'message' => 'Paiement expiré'
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

        $result = DB::transaction(function () use ($paiement, $utilisateur) {
            $portefeuille = $utilisateur->portefeuille;
            $marchand = $paiement->marchand;

            // Débiter l'utilisateur
            $portefeuille->decrement('solde', $paiement->montant);

            // Créditer le marchand (simulé)
            // $marchand->portefeuille->increment('solde', $paiement->montant);

            // Créer la transaction
            $idTransaction = 'txn_' . Str::random(10);
            Transaction::create([
                'idTransaction' => $idTransaction,
                'idUtilisateur' => $utilisateur->id,
                'type' => 'paiement',
                'montant' => $paiement->montant,
                'devise' => $paiement->devise,
                'nomMarchand' => $marchand->nom,
                'categorieMarchand' => $marchand->categorie,
                'statut' => 'termine',
                'dateTransaction' => Carbon::now(),
                'reference' => 'OM' . date('YmdHis') . rand(100000, 999999),
                'frais' => 0,
            ]);

            // Mettre à jour le paiement
            $paiement->update([
                'statut' => 'termine',
                'idTransaction' => $idTransaction,
            ]);

            // Marquer le code comme utilisé si c'était un paiement par code
            if ($paiement->modePaiement === 'code') {
                // Trouver et marquer le code comme utilisé
            }

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
                'marchand' => [
                    'nom' => $paiement->marchand->nom,
                    'numeroTelephone' => $paiement->marchand->numeroTelephone,
                ],
                'montant' => $paiement->montant,
                'dateTransaction' => Carbon::now()->toIso8601String(),
                'reference' => $result['reference'],
                'recu' => 'https://cdn.ompay.sn/recus/' . $result['idTransaction'] . '.pdf',
            ],
            'message' => 'Paiement effectué avec succès'
        ];
    }

    // 4.6 Annuler un Paiement
    public function annulerPaiement($utilisateur, $idPaiement)
    {
        $paiement = Paiement::where('idPaiement', $idPaiement)
                            ->where('idUtilisateur', $utilisateur->id)
                            ->where('statut', 'en_attente_confirmation')
                            ->first();

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

        if (Carbon::now()->isAfter($paiement->dateExpiration)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_009',
                    'message' => 'Paiement expiré'
                ],
                'status' => 422
            ];
        }

        $paiement->update(['statut' => 'annule']);

        return [
            'success' => true,
            'message' => 'Paiement annulé avec succès'
        ];
    }
}