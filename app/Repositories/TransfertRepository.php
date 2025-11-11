<?php

namespace App\Repositories;

use App\Models\Transfert;
use App\Interfaces\TransfertRepositoryInterface;

class TransfertRepository implements TransfertRepositoryInterface
{
    public function create(array $data): Transfert
    {
        return Transfert::create($data);
    }

    public function findById(string $id): ?Transfert
    {
        return Transfert::find($id);
    }

    public function findByTransactionId(string $transactionId): ?Transfert
    {
        return Transfert::where('id_transaction', $transactionId)->first();
    }

    public function findByUserId(string $userId, string $id): ?Transfert
    {
        return Transfert::where('id', $id)
                       ->where('id_expediteur', $userId)
                       ->first();
    }
}