<?php

namespace App\Interfaces;

use App\Models\QRCode;

interface QRCodeRepositoryInterface
{
    public function create(array $data): QRCode;
}