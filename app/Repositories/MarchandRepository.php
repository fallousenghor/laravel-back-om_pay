<?php

namespace App\Repositories;

use App\Models\Marchand;
use App\Interfaces\MarchandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MarchandRepository implements MarchandRepositoryInterface
{
    public function findById(int $id): ?Marchand
    {
        return Marchand::byId($id)->first();
    }

    public function getAllGroupedByCategory(): Collection
    {
        return Marchand::all()->groupBy('categorie');
    }

    public function findByCategory(string $category): Collection
    {
        return Marchand::byCategory($category)->get();
    }
}