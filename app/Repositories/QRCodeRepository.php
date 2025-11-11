<?php

namespace App\Repositories;

use App\Models\QRCode;
use App\Interfaces\QRCodeRepositoryInterface;

class QRCodeRepository implements QRCodeRepositoryInterface
{
    public function create(array $data): QRCode
    {
        return QRCode::create($data);
    }
}