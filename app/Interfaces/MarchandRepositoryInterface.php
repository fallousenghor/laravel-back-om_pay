<?php

namespace App\Interfaces;

use App\Models\Marchand;
use Illuminate\Database\Eloquent\Collection;

interface MarchandRepositoryInterface
{
    public function findById(int $id): ?Marchand;
    public function getAllGroupedByCategory(): Collection;
    public function findByCategory(string $category): Collection;
}