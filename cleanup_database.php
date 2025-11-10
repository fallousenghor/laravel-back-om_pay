<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // Désactiver les contraintes de clé étrangère
    DB::statement('SET CONSTRAINTS ALL DEFERRED');

    // Tables à supprimer
    $tables = [
        'contacts',
        'paiements',
        'qr_codes',
        'code_paiements',
        'marchands',
        'transferts',
        'destinataires',
        'transactions',
        'portefeuilles',
        'authentifications',
        'parametres_securites'
    ];

    // Supprimer les tables
    foreach ($tables as $table) {
        DB::statement("DROP TABLE IF EXISTS $table CASCADE");
    }

    // Vider la table utilisateurs
    DB::table('utilisateurs')->truncate();
    
    // Vider et recréer les comptes Orange Money
    DB::table('orange_money')->truncate();
    
    // Créer des comptes Orange Money de test
    $comptes = [
        [
            'numero_telephone' => '+221771234567',
            'prenom' => 'Moussa',
            'nom' => 'Diop',
            'numero_cni' => '1234567890123',
            'solde' => 150000,
            'status' => 'active'
        ],
        [
            'numero_telephone' => '+221772345678',
            'prenom' => 'Fatou',
            'nom' => 'Sall',
            'numero_cni' => '2345678901234',
            'solde' => 200000,
            'status' => 'active'
        ],
        [
            'numero_telephone' => '+221773456789',
            'prenom' => 'Amadou',
            'nom' => 'Ba',
            'numero_cni' => '3456789012345',
            'solde' => 300000,
            'status' => 'active'
        ]
    ];

    foreach ($comptes as $compte) {
        DB::table('orange_money')->insert($compte);
        echo "Compte créé pour : " . $compte['prenom'] . " " . $compte['nom'] . "\n";
    }

    echo "\nBase de données nettoyée et comptes Orange Money créés avec succès.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}