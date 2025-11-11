<?php

namespace App\Repositories;

use App\Models\Portefeuille;
use App\Interfaces\PortefeuilleRepositoryInterface;

class PortefeuilleRepository implements PortefeuilleRepositoryInterface
{
    public function findOrCreateByUserId(int $userId, array $data = []): Portefeuille
    {
        return Portefeuille::firstOrCreate(
            ['id_utilisateur' => $userId],
            array_merge(['solde' => 0, 'devise' => 'XOF'], $data)
        );
    }
}