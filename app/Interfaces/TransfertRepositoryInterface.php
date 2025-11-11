<?php

namespace App\Interfaces;

use App\Models\Transfert;

interface TransfertRepositoryInterface
{
    public function create(array $data): Transfert;
    public function findById(string $id): ?Transfert;
    public function findByTransactionId(string $transactionId): ?Transfert;
    public function findByUserId(string $userId, string $id): ?Transfert;
}