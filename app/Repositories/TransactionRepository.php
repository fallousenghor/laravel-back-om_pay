<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Interfaces\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function findById(string $id): ?Transaction
    {
        return Transaction::find($id);
    }

    public function findByReference(string $reference): ?Transaction
    {
        return Transaction::byReference($reference)->first();
    }

    public function findByUserId(string $userId, array $filters = [], int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        $query = Transaction::byUser($userId);

        if (isset($filters['type']) && $filters['type'] !== 'tous') {
            $query->parType($filters['type']);
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

        return $query->orderBy('date_transaction', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);
    }

    public function updateStatus(Transaction $transaction, string $status): bool
    {
        return $transaction->update(['statut' => $status]);
    }

    public function delete(Transaction $transaction): bool
    {
        return $transaction->delete();
    }
}