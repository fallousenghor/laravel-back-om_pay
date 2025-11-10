<?php

namespace App\Services;

use Carbon\Carbon;

class OtpService
{
    // Générer un OTP
    public function generateOtp()
    {
        return rand(100000, 999999);
    }

    // Vérifier un OTP
    public function verifyOtp($utilisateur, $codeOTP)
    {
        if (!$utilisateur ||
            $utilisateur->otp != $codeOTP ||
            ($utilisateur->otp_expires_at && Carbon::now()->isAfter($utilisateur->otp_expires_at))) {
            return false;
        }

        return true;
    }

    // Invalider un OTP
    public function invalidateOtp($utilisateur)
    {
        $utilisateur->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return true;
    }

    // Régénérer un OTP
    public function regenerateOtp($utilisateur)
    {
        $otp = $this->generateOtp();

        $utilisateur->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        return $otp;
    }
}