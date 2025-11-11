<?php

namespace App\Interfaces;

use App\Models\VerificationCode;

interface VerificationCodeRepositoryInterface
{
    public function create(array $data): VerificationCode;
    public function findValidByTokenAndCode(string $token, string $code): ?VerificationCode;
    public function findByTokenAndPhone(string $token, string $numeroTelephone): ?VerificationCode;
    public function markAsUsed(VerificationCode $verificationCode): bool;
}