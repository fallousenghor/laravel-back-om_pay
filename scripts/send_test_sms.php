<?php

// Bootstrap Laravel so helpers like config() and Log are available
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Use the service container to resolve the Twilio service
try {
    /** @var \App\Services\TwilioSmsService $svc */
    $svc = $app->make(\App\Services\TwilioSmsService::class);

    $to = '+221782463262'; // number provided by user
    $message = __('messages.fr.sms.test_sms', ['date' => date('Y-m-d H:i:s')]);

    $ok = $svc->sendSms($to, $message);

    if ($ok) {
        echo __('messages.fr.sms.sms_sent_success', ['phone' => $to]) . "\n";
    } else {
        echo "Échec de l'envoi du SMS à {$to} (voir logs).\n";
    }
} catch (Throwable $e) {
    echo "Exception lors de l'envoi du SMS: " . $e->getMessage() . "\n";
}
