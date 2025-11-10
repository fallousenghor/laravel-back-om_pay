<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\OrangeMoney;

try {
    $compte = OrangeMoney::create([
        'numero_telephone' => '+221771000001',
        'prenom' => 'John',
        'nom' => 'Doe',
        'solde' => 100000,
        'status' => 'active'
    ]);
    echo "Compte Orange Money créé: ".json_encode($compte->toArray())."\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}