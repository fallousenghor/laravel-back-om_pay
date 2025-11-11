<?php

namespace App\Interfaces;

use App\Models\Portefeuille;

interface PortefeuilleRepositoryInterface
{
    public function findOrCreateByUserId(int $userId, array $data = []): Portefeuille;
}