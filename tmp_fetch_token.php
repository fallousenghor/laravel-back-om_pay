<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Authentification;

$a = Authentification::orderBy('id', 'desc')->first();
if ($a) {
    echo $a->jeton_acces . PHP_EOL;
} else {
    echo "" . PHP_EOL;
}
