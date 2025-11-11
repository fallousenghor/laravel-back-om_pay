<?php

namespace App\Interfaces;

interface AuthenticationServiceInterface
{
    public function initiateLogin(string $numero_telephone);
    public function verifyCode(string $token, string $code);
    public function completeLogin($verification);
}
