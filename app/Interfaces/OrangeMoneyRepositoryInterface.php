<?php

namespace App\Interfaces;

use App\Models\OrangeMoney;

interface OrangeMoneyRepositoryInterface
{
    public function findByPhoneNumber(string $numeroTelephone): ?OrangeMoney;
}