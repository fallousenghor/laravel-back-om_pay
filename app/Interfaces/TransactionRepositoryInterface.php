<?php

namespace App\Interfaces;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    public function create(array $data): Transaction;
    public function findById(string $id): ?Transaction;
    public function findByReference(string $reference): ?Transaction;
    public function findByUserId(string $userId, array $filters = [], int $page = 1, int $perPage = 10): LengthAwarePaginator;
    public function updateStatus(Transaction $transaction, string $status): bool;
    public function delete(Transaction $transaction): bool;
}