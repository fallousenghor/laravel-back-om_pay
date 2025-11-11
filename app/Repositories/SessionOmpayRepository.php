<?php

namespace App\Repositories;

use App\Models\SessionOmpay;
use App\Interfaces\SessionOmpayRepositoryInterface;

class SessionOmpayRepository implements SessionOmpayRepositoryInterface
{
    public function create(array $data): SessionOmpay
    {
        return SessionOmpay::create($data);
    }

    public function delete(SessionOmpay $session): bool
    {
        return $session->delete();
    }
}