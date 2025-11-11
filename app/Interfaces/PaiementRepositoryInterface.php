<?php

namespace App\Interfaces;

use App\Models\Paiement;

interface PaiementRepositoryInterface
{
    public function create(array $data): Paiement;
    public function findById(string $id): ?Paiement;
    public function findByTransactionId(string $transactionId): ?Paiement;
}