<?php

namespace App\Services;

use App\Models\Transaction;
use App\Interfaces\HistoriqueServiceInterface;

class HistoriqueService implements HistoriqueServiceInterface
{
    // 6.1 Rechercher dans l'Historique
    public function rechercher($utilisateur, $filters)
    {
        $query = Transaction::where('idUtilisateur', $utilisateur->id);

        if (isset($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('reference', 'like', "%{$q}%")
                         ->orWhere('numeroTelephoneDestinataire', 'like', "%{$q}%")
                         ->orWhere('nomDestinataire', 'like', "%{$q}%")
                         ->orWhere('nomMarchand', 'like', "%{$q}%");
            });
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['montantMin'])) {
            $query->where('montant', '>=', $filters['montantMin']);
        }

        if (isset($filters['montantMax'])) {
            $query->where('montant', '<=', $filters['montantMax']);
        }

        if (isset($filters['dateDebut'])) {
            $query->whereDate('dateTransaction', '>=', $filters['dateDebut']);
        }

        if (isset($filters['dateFin'])) {
            $query->whereDate('dateTransaction', '<=', $filters['dateFin']);
        }

        $resultats = $query->orderBy('dateTransaction', 'desc')
                           ->limit(100) // Limiter les résultats
                           ->get();

        $data = $resultats->map(function ($transaction) {
            $destinataire = null;
            if ($transaction->type === 'transfert') {
                $destinataire = [
                    'nom' => $transaction->nomDestinataire,
                    'numeroTelephone' => $transaction->numeroTelephoneDestinataire,
                ];
            }

            return [
                'idTransaction' => $transaction->idTransaction,
                'type' => $transaction->type,
                'montant' => $transaction->montant,
                'destinataire' => $destinataire,
                'dateTransaction' => $transaction->dateTransaction->toISOString(),
                'reference' => $transaction->reference,
            ];
        });

        return [
            'success' => true,
            'data' => [
                'resultats' => $data,
                'nombreResultats' => $data->count(),
            ]
        ];
    }
}