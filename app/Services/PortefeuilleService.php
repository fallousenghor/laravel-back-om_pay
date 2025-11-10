<?php

namespace App\Services;

use App\Models\Portefeuille;
use App\Models\Transaction;
use App\Interfaces\PortefeuilleServiceInterface;
use Carbon\Carbon;

class PortefeuilleService implements PortefeuilleServiceInterface
{
    // 2.1 Consulter le Solde
    public function consulterSolde($utilisateur)
    {
        $portefeuille = $utilisateur->portefeuille;

        if (!$portefeuille) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'WALLET_001',
                    'message' => 'Portefeuille introuvable'
                ],
                'status' => 404
            ];
        }

        return [
            'success' => true,
            'data' => $portefeuille->solde
        ];
    }

    // 2.2 Historique des Transactions
    public function historiqueTransactions($utilisateur, $filters, $page, $limite)
    {
        $query = Transaction::where('id_utilisateur', $utilisateur->id);

        if (isset($filters['type']) && $filters['type'] !== 'tous') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['dateDebut'])) {
            $query->whereDate('date_transaction', '>=', $filters['dateDebut']);
        }

        if (isset($filters['dateFin'])) {
            $query->whereDate('date_transaction', '<=', $filters['dateFin']);
        }

        if (isset($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        $transactions = $query->orderBy('date_transaction', 'desc')
                              ->paginate($limite, ['*'], 'page', $page);

        $data = $transactions->map(function ($transaction) {
            $destinataire = null;
            $marchand = null;

            if ($transaction->type === 'transfert') {
                $transfert = $transaction->transfert;
                if ($transfert) {
                    $destinataire = [
                        'numeroTelephone' => $transfert->destinataire->numero_telephone,
                        'nom' => $transfert->nom_destinataire,
                    ];
                }
            } elseif ($transaction->type === 'paiement') {
                $paiement = $transaction->paiement;
                if ($paiement) {
                    $marchand = [
                        'nom' => $paiement->marchand->nom,
                        'categorie' => 'General', // Assuming no category in marchand table
                    ];
                }
            }

            return [
                'idTransaction' => $transaction->id,
                'type' => $transaction->type,
                'montant' => $transaction->montant,
                'devise' => $transaction->devise,
                'destinataire' => $destinataire,
                'marchand' => $marchand,
                'statut' => $transaction->statut,
                'dateTransaction' => $transaction->date_transaction->toISOString(),
                'reference' => $transaction->reference,
                'frais' => $transaction->frais,
            ];
        });

        return [
            'success' => true,
            'data' => [
                'transactions' => $data,
                'pagination' => [
                    'pageActuelle' => $transactions->currentPage(),
                    'totalPages' => $transactions->lastPage(),
                    'totalElements' => $transactions->total(),
                    'elementsParPage' => $transactions->perPage(),
                ]
            ]
        ];
    }

    // 2.3 Détails d'une Transaction
    public function detailsTransaction($utilisateur, $idTransaction)
    {
        $transaction = Transaction::where('id', $idTransaction)
                                  ->where('id_utilisateur', $utilisateur->id)
                                  ->first();

        if (!$transaction) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'WALLET_001',
                    'message' => 'Transaction introuvable'
                ],
                'status' => 404
            ];
        }

        $expediteur = null;
        $destinataire = null;

        if ($transaction->type === 'transfert') {
            $expediteur = [
                'numeroTelephone' => $utilisateur->numero_telephone,
                'nom' => $utilisateur->prenom . ' ' . $utilisateur->nom,
            ];
            $transfert = $transaction->transfert;
            if ($transfert) {
                $destinataire = [
                    'numeroTelephone' => $transfert->destinataire->numero_telephone,
                    'nom' => $transfert->nom_destinataire,
                ];
            }
        }

        return [
            'success' => true,
            'data' => [
                'idTransaction' => $transaction->id,
                'type' => $transaction->type,
                'montant' => $transaction->montant,
                'devise' => $transaction->devise,
                'expediteur' => $expediteur,
                'destinataire' => $destinataire,
                'statut' => $transaction->statut,
                'dateTransaction' => $transaction->date_transaction->toISOString(),
                'reference' => $transaction->reference,
                'frais' => $transaction->frais,
                'note' => $transaction->transfert ? $transaction->transfert->note : null,
            ]
        ];
    }
}