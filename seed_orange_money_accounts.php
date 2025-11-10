<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrangeMoney;
use Illuminate\Support\Facades\DB;

try {
    // Vider la table orange_money
    DB::table('orange_money')->truncate();
    
    // Données de test pour les comptes Orange Money
    $comptes = [
        [
            'numero_telephone' => '+221776543210',
            'prenom' => 'Fatou',
            'nom' => 'Diallo',
            'numero_cni' => '1234567890123',
            'solde' => 250000,
            'status' => 'active'
        ],
        [
            'numero_telephone' => '+221777654321',
            'prenom' => 'Abdou',
            'nom' => 'Ndiaye',
            'numero_cni' => '2345678901234',
            'solde' => 175000,
            'status' => 'active'
        ],
        [
            'numero_telephone' => '+221778765432',
            'prenom' => 'Marie',
            'nom' => 'Sow',
            'numero_cni' => '3456789012345',
            'solde' => 320000,
            'status' => 'active'
        ],
        [
            'numero_telephone' => '+221779876543',
            'prenom' => 'Omar',
            'nom' => 'Fall',
            'numero_cni' => '4567890123456',
            'solde' => 450000,
            'status' => 'active'
        ],
        [
            'numero_telephone' => '+221770987654',
            'prenom' => 'Aïda',
            'nom' => 'Diop',
            'numero_cni' => '5678901234567',
            'solde' => 280000,
            'status' => 'active'
        ]
    ];

    // Créer les comptes
    foreach ($comptes as $compte) {
        $compte_om = OrangeMoney::create($compte);
        echo "Compte créé: {$compte['prenom']} {$compte['nom']} - {$compte['numero_telephone']}\n";
        echo "Solde: " . number_format($compte['solde'], 0, ',', ' ') . " FCFA\n";
        echo "--------------------------------\n";
    }

    echo "\nNombre total de comptes créés: " . count($comptes) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}