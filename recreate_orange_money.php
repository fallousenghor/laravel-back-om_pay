<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

try {
    // Supprimer et recréer la table orange_money
    DB::statement('DROP TABLE IF EXISTS orange_money CASCADE');
    DB::statement('
        CREATE TABLE orange_money (
            id UUID PRIMARY KEY,
            numero_telephone VARCHAR(255) UNIQUE,
            nom VARCHAR(255),
            prenom VARCHAR(255),
            numero_cni VARCHAR(255) UNIQUE,
            solde DECIMAL(15,2) DEFAULT 0,
            status VARCHAR(255) DEFAULT \'active\',
            created_at TIMESTAMP,
            updated_at TIMESTAMP
        )
    ');
    
    // Données de test
    $comptes = [
        [
            'id' => Str::uuid(),
            'numero_telephone' => '+221776543210',
            'prenom' => 'Fatou',
            'nom' => 'Diallo',
            'numero_cni' => '1234567890123',
            'solde' => 250000,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => Str::uuid(),
            'numero_telephone' => '+221777654321',
            'prenom' => 'Abdou',
            'nom' => 'Ndiaye',
            'numero_cni' => '2345678901234',
            'solde' => 175000,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => Str::uuid(),
            'numero_telephone' => '+221778765432',
            'prenom' => 'Marie',
            'nom' => 'Sow',
            'numero_cni' => '3456789012345',
            'solde' => 320000,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => Str::uuid(),
            'numero_telephone' => '+221779876543',
            'prenom' => 'Omar',
            'nom' => 'Fall',
            'numero_cni' => '4567890123456',
            'solde' => 450000,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => Str::uuid(),
            'numero_telephone' => '+221770987654',
            'prenom' => 'Aïda',
            'nom' => 'Diop',
            'numero_cni' => '5678901234567',
            'solde' => 280000,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];

    // Insérer les données
    foreach ($comptes as $compte) {
        DB::table('orange_money')->insert($compte);
        echo "Compte créé: {$compte['prenom']} {$compte['nom']} - {$compte['numero_telephone']}\n";
        echo "Solde: " . number_format($compte['solde'], 0, ',', ' ') . " FCFA\n";
        echo "CNI: {$compte['numero_cni']}\n";
        echo "--------------------------------\n";
    }

    echo "\nNombre total de comptes créés: " . count($comptes) . "\n";
    
    // Vérifier les données
    $total = DB::table('orange_money')->count();
    echo "Nombre total de comptes dans la base: $total\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}