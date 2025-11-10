<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Création de 5 comptes Orange Money...\n";

$accounts = [
    [
        'numero_telephone' => '771234567',
        'solde' => 50000.00,
        'nom' => 'Diallo',
        'prenom' => 'Mamadou',
        'code_pin' => '1234'
    ],
    [
        'numero_telephone' => '772345678',
        'solde' => 75000.00,
        'nom' => 'Sow',
        'prenom' => 'Fatou',
        'code_pin' => '5678'
    ],
    [
        'numero_telephone' => '763456789',
        'solde' => 30000.00,
        'nom' => 'Ndiaye',
        'prenom' => 'Ibrahima',
        'code_pin' => '9012'
    ],
    [
        'numero_telephone' => '701234567',
        'solde' => 100000.00,
        'nom' => 'Ba',
        'prenom' => 'Aminata',
        'code_pin' => '3456'
    ],
    [
        'numero_telephone' => '752345678',
        'solde' => 25000.00,
        'nom' => 'Gueye',
        'prenom' => 'Cheikh',
        'code_pin' => '7890'
    ]
];

// Créer les utilisateurs et les comptes Orange Money liés
echo "Création de 5 utilisateurs liés à des comptes Orange Money...\n";

foreach ($accounts as $account) {
    try {
        $userId = (string) Str::uuid();

        // Créer l'utilisateur
        DB::table('utilisateurs')->insert([
            'id' => $userId,
            'numero_telephone' => $account['numero_telephone'],
            'prenom' => $account['prenom'],
            'nom' => $account['nom'],
            'email' => $account['prenom'] . '.' . $account['nom'] . rand(1000, 9999) . '@example.com',
            'code_pin' => bcrypt($account['code_pin']),
            'numero_cni' => (string) rand(100000000000, 999999999999),
            'statut_kyc' => 'verifie',
            'biometrie_activee' => rand(0, 1),
            'date_creation' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer le portefeuille
        DB::table('portefeuilles')->insert([
            'id' => (string) Str::uuid(),
            'id_utilisateur' => $userId,
            'solde' => $account['solde'],
            'devise' => 'XOF',
            'derniere_mise_a_jour' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer les paramètres de sécurité
        DB::table('parametres_securites')->insert([
            'id' => (string) Str::uuid(),
            'id_utilisateur' => $userId,
            'biometrie_active' => rand(0, 1),
            'tentatives_echouees' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un compte Orange Money lié
        DB::table('orange_money')->insert([
            'id' => (string) Str::uuid(),
            'numero_telephone' => $account['numero_telephone'],
            'solde' => $account['solde'],
            'nom' => $account['nom'],
            'prenom' => $account['prenom'],
            'numero_cni' => (string) rand(100000000000, 999999999999),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✓ Utilisateur et compte OM créés pour {$account['prenom']} {$account['nom']} ({$account['numero_telephone']})\n";

    } catch (Exception $e) {
        echo "✗ Erreur pour {$account['numero_telephone']}: " . $e->getMessage() . "\n";
    }
}

echo "\nCréation terminée!\n";

foreach ($accounts as $account) {
    try {
        $userId = (string) Str::uuid();

        // Créer l'utilisateur
        DB::table('utilisateurs')->insert([
            'id' => $userId,
            'numero_telephone' => $account['numero_telephone'],
            'prenom' => $account['prenom'],
            'nom' => $account['nom'],
            'email' => $account['prenom'] . '.' . $account['nom'] . rand(100, 999) . '@example.com',
            'code_pin' => bcrypt($account['code_pin']),
            'numero_cni' => (string) rand(100000000000, 999999999999),
            'statut_kyc' => 'verifie',
            'biometrie_activee' => rand(0, 1),
            'date_creation' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer le portefeuille
        DB::table('portefeuilles')->insert([
            'id' => (string) Str::uuid(),
            'id_utilisateur' => $userId,
            'solde' => $account['solde'],
            'devise' => 'XOF',
            'derniere_mise_a_jour' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer les paramètres de sécurité
        DB::table('parametres_securites')->insert([
            'id' => (string) Str::uuid(),
            'id_utilisateur' => $userId,
            'biometrie_active' => rand(0, 1),
            'tentatives_echouees' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Créer un compte Orange Money
        DB::table('orange_money')->insert([
            'id' => (string) Str::uuid(),
            'numero_telephone' => $account['numero_telephone'],
            'solde' => $account['solde'],
            'nom' => $account['nom'],
            'prenom' => $account['prenom'],
            'numero_cni' => (string) rand(100000000000, 999999999999),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✓ Compte créé pour {$account['prenom']} {$account['nom']} ({$account['numero_telephone']})\n";

    } catch (Exception $e) {
        echo "✗ Erreur pour {$account['numero_telephone']}: " . $e->getMessage() . "\n";
    }
}

echo "\nCréation terminée!\n";