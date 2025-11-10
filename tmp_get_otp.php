<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Utilisateur;

$phone = $argv[1] ?? null;
if (!$phone) {
    echo "Usage: php tmp_get_otp.php +221...\n";
    exit(1);
}

$u = Utilisateur::where('numero_telephone', $phone)->first();
if (!$u) {
    echo "User not found\n";
    exit(2);
}

echo "OTP for {$phone}: ";
var_export($u->otp);
echo "\n";
