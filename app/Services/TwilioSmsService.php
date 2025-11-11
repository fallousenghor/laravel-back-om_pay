<?php

namespace App\Services;

use App\Interfaces\TwilioSmsServiceInterface;
use Twilio\Rest\Client;
use Exception;

class TwilioSmsService implements TwilioSmsServiceInterface
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $sid = config('services.twilio.sid') ?: env('TWILIO_SID');
        $token = config('services.twilio.token') ?: env('TWILIO_AUTH_TOKEN');
        $this->from = config('services.twilio.from') ?: env('TWILIO_NUMBER');

        $this->client = new Client($sid, $token);
    }

    /**
     * Send an SMS using Twilio
     *
     * @param string $to E.164 phone number
     * @param string $message
     * @return bool true on success, false on failure
     */
    public function sendSms(string $to, string $message): bool
    {
        try {
            $msg = $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message,
            ]);

            // Log Twilio message SID for tracing
            \Log::info('TwilioSmsService sent message', ['sid' => $msg->sid ?? null, 'to' => $to]);

            return true;
        } catch (Exception $e) {
            \Log::error('TwilioSmsService sendSms error: ' . $e->getMessage(), ['to' => $to]);
            return false;
        }
    }
}
