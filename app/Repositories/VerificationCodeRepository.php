<?php

namespace App\Repositories;

use App\Models\VerificationCode;
use App\Interfaces\VerificationCodeRepositoryInterface;
use Carbon\Carbon;

class VerificationCodeRepository implements VerificationCodeRepositoryInterface
{
    public function create(array $data): VerificationCode
    {
        return VerificationCode::create($data);
    }

    public function findValidByTokenAndCode(string $token, string $code): ?VerificationCode
    {
        return VerificationCode::where('token', $token)
            ->where('code', $code)
            ->where('used', false)
            ->where('expire_at', '>', Carbon::now())
            ->first();
    }

    public function findByTokenAndPhone(string $token, string $numeroTelephone): ?VerificationCode
    {
        return VerificationCode::where('token', $token)
            ->where('numero_telephone', $numeroTelephone)
            ->where('used', true)
            ->first();
    }

    public function markAsUsed(VerificationCode $verificationCode): bool
    {
        return $verificationCode->update(['used' => true]);
    }
}