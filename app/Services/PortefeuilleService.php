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
        // Récupérer les transactions où l'utilisateur est l'expéditeur
        $queryExpediteur = Transaction::where('id_utilisateur', $utilisateur->id);

        // Récupérer les transactions de transfert où l'utilisateur est le destinataire
        $queryDestinataire = Transaction::where('type', 'transfert')
            ->whereHas('transfert', function ($q) use ($utilisateur) {
                $q->where('numero_telephone_destinataire', $utilisateur->numero_telephone);
            });

        // Utiliser union pour combiner les résultats
        $query = $queryExpediteur->union($queryDestinataire);

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

        $transactions = $query->with(['transfert', 'paiement.marchand'])
                              ->orderBy('date_transaction', 'desc')
                              ->get(); // On récupère d'abord tous les résultats

        // Grouper et paginer manuellement pour éviter les problèmes avec union
        $allTransactions = $transactions->sortByDesc('date_transaction');
        $paginatedTransactions = collect($allTransactions)->forPage($page, $limite);
        $totalCount = $allTransactions->count();

        $data = $transactions->map(function ($transaction) use ($utilisateur) {
            $destinataire = null;
            $expediteur = null;
            $marchand = null;
            $montantAffiche = $transaction->montant;
            $typeOperation = 'debit'; // Par défaut débit

            if ($transaction->type === 'transfert') {
                $transfert = $transaction->transfert;
                if ($transfert) {
                    // Vérifier si l'utilisateur est l'expéditeur ou le destinataire
                    if ($transfert->id_utilisateur_expediteur === $utilisateur->id) {
                        // L'utilisateur est l'expéditeur -> débit
                        $typeOperation = 'debit';
                        $destinataire = [
                            'numeroTelephone' => $transfert->numero_telephone_destinataire,
                            'nom' => $transfert->nom_destinataire,
                        ];
                    } else {
                        // L'utilisateur est le destinataire -> crédit
                        $typeOperation = 'credit';
                        $expediteur = [
                            'numeroTelephone' => $transfert->numero_telephone_expediteur ?? 'Inconnu',
                            'nom' => $transfert->nom_expediteur ?? 'Inconnu',
                        ];
                    }
                } elseif ($transaction->numero_telephone_destinataire && $transaction->nom_destinataire) {
                    // Fallback to transaction fields if transfert relation is null
                    $typeOperation = 'debit'; // Par défaut débit si pas d'info détaillée
                    $destinataire = [
                        'numeroTelephone' => $transaction->numero_telephone_destinataire,
                        'nom' => $transaction->nom_destinataire,
                    ];
                }
            } elseif ($transaction->type === 'paiement') {
                // Les paiements sont toujours des débits pour l'utilisateur
                $typeOperation = 'debit';
                $paiement = $transaction->paiement;
                if ($paiement && $paiement->marchand) {
                    $marchand = [
                        'nom' => $paiement->marchand->nom,
                        'categorie' => $transaction->categorie_marchand ?? 'General',
                    ];
                } elseif ($transaction->nom_marchand && $transaction->categorie_marchand) {
                    // Fallback to transaction fields if paiement relation is null
                    $marchand = [
                        'nom' => $transaction->nom_marchand,
                        'categorie' => $transaction->categorie_marchand,
                    ];
                }
            }

            // Appliquer le signe selon le type d'opération
            $montantAffiche = $typeOperation === 'credit' ? '+' . $transaction->montant : '-' . $transaction->montant;

            return [
                'idTransaction' => $transaction->id,
                'type' => $transaction->type,
                'montant' => $montantAffiche,
                'montantNumerique' => $transaction->montant, // Garder le montant numérique pour les calculs
                'devise' => $transaction->devise,
                'typeOperation' => $typeOperation, // 'debit' ou 'credit'
                'expediteur' => $expediteur,
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
                    'pageActuelle' => $page,
                    'totalPages' => ceil($totalCount / $limite),
                    'totalElements' => $totalCount,
                    'elementsParPage' => $limite,
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
                    'numeroTelephone' => $transfert->numero_telephone_destinataire,
                    'nom' => $transfert->nom_destinataire,
                ];
            } elseif ($transaction->numero_telephone_destinataire && $transaction->nom_destinataire) {
                // Fallback to transaction fields if transfert relation is null
                $destinataire = [
                    'numeroTelephone' => $transaction->numero_telephone_destinataire,
                    'nom' => $transaction->nom_destinataire,
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