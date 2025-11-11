<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\VerificationCode;

$codes = VerificationCode::orderBy('created_at', 'desc')->take(20)->get();

foreach ($codes as $c) {
    echo $c->id . ' | ' . $c->numero_telephone . ' | ' . $c->code . ' | ' . $c->token . ' | used:' . ($c->used?1:0) . ' | expires:' . $c->expire_at . "\n";
}
