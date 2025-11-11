<?php

namespace App\Interfaces;

use App\Models\SessionOmpay;

interface SessionOmpayRepositoryInterface
{
    public function create(array $data): SessionOmpay;
    public function delete(SessionOmpay $session): bool;
}