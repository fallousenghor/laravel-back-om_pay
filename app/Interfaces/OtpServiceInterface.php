<?php

namespace App\Interfaces;

interface OtpServiceInterface
{
    public function generateOtp();
    public function verifyOtp($utilisateur, $codeOTP);
    public function invalidateOtp($utilisateur);
    public function regenerateOtp($utilisateur);
    public function sendOtpBySms($utilisateur): bool;
}
