<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\OrangeMoney;
use Illuminate\Support\Facades\DB;

try {
    // Vider toutes les tables existantes
    DB::table('utilisateurs')->truncate();
    DB::table('orange_money')->truncate();
    
    // Créer des comptes Orange Money de test
    $comptes = [
        [
            'numero_telephone' => '+221771234567',
            'prenom' => 'Moussa',
            'nom' => 'Diop',
            'solde' => 150000,
            'status' => 'active'
        ],
        [
            'numero_telephone' => '+221772345678',
            'prenom' => 'Fatou',
            'nom' => 'Sall',
            'solde' => 200000,
            'status' => 'active'
        ],
        [
            'numero_telephone' => '+221773456789',
            'prenom' => 'Amadou',
            'nom' => 'Ba',
            'solde' => 300000,
            'status' => 'active'
        ]
    ];

    foreach ($comptes as $compte) {
        OrangeMoney::create($compte);
        echo "Compte créé pour : " . $compte['prenom'] . " " . $compte['nom'] . "\n";
    }

    echo "\nNombre total de comptes Orange Money créés : " . count($comptes) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}