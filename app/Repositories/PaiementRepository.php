<?php

namespace App\Repositories;

use App\Models\Paiement;
use App\Interfaces\PaiementRepositoryInterface;

class PaiementRepository implements PaiementRepositoryInterface
{
    public function create(array $data): Paiement
    {
        return Paiement::create($data);
    }

    public function findById(string $id): ?Paiement
    {
        return Paiement::find($id);
    }

    public function findByTransactionId(string $transactionId): ?Paiement
    {
        return Paiement::where('id', $transactionId)->first();
    }
}