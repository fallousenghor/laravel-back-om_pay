<?php

namespace App\Repositories;

use App\Models\OrangeMoney;
use App\Interfaces\OrangeMoneyRepositoryInterface;

class OrangeMoneyRepository implements OrangeMoneyRepositoryInterface
{
    public function findByPhoneNumber(string $numeroTelephone): ?OrangeMoney
    {
        return OrangeMoney::byPhoneNumber($numeroTelephone)->first();
    }
}