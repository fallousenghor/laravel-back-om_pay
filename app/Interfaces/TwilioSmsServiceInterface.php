<?php

namespace App\Interfaces;

interface TwilioSmsServiceInterface
{
    public function sendSms(string $to, string $message): bool;
}
