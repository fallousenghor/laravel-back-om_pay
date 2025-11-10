<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Utilisateur;
use App\Models\Portefeuille;
use App\Models\Transaction;
use App\Models\OrangeMoney;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

try {
    echo "=== CRÉATION DES DONNÉES DE TEST ===\n\n";

    // 1. Vider les tables existantes
    echo "1. Nettoyage des données existantes...\n";
    DB::table('transactions')->truncate();
    DB::table('portefeuilles')->truncate();
    DB::table('utilisateurs')->truncate();

    // 2. Créer 5 utilisateurs OM Pay
    echo "2. Création des utilisateurs OM Pay...\n";

    $orangeMoneyAccounts = OrangeMoney::all();
    $utilisateurs = [];

    foreach ($orangeMoneyAccounts as $omAccount) {
        $utilisateur = Utilisateur::create([
            'numero_telephone' => $omAccount->numero_telephone,
            'prenom' => $omAccount->prenom,
            'nom' => $omAccount->nom,
            'email' => strtolower($omAccount->prenom . '.' . $omAccount->nom) . '@gmail.com',
            'code_pin' => Hash::make('1234'), // PIN par défaut: 1234
            'numero_cni' => $omAccount->numero_cni,
            'statut_kyc' => 'verifie'
        ]);

        // Créer le portefeuille
        Portefeuille::create([
            'id_utilisateur' => $utilisateur->id,
            'solde' => $omAccount->solde,
            'devise' => 'XOF',
            'derniere_mise_a_jour' => now()
        ]);

        $utilisateurs[] = $utilisateur;
        echo "✓ Utilisateur créé: {$utilisateur->prenom} {$utilisateur->nom} - {$utilisateur->numero_telephone}\n";
    }

    echo "\n2. Création des transactions de test...\n";

    // 3. Créer 10 transactions diverses
    $transactions = [
        // Transferts entre utilisateurs
        [
            'type' => 'transfert',
            'from_user' => 0, // Fatou
            'to_user' => 1,   // Abdou
            'montant' => 50000,
            'note' => 'Paiement dette'
        ],
        [
            'type' => 'transfert',
            'from_user' => 2, // Marie
            'to_user' => 3,   // Omar
            'montant' => 25000,
            'note' => 'Participation événement'
        ],
        [
            'type' => 'transfert',
            'from_user' => 4, // Aïda
            'to_user' => 0,   // Fatou
            'montant' => 15000,
            'note' => 'Remboursement'
        ],
        [
            'type' => 'transfert',
            'from_user' => 1, // Abdou
            'to_user' => 2,   // Marie
            'montant' => 30000,
            'note' => 'Achat téléphone'
        ],
        [
            'type' => 'transfert',
            'from_user' => 3, // Omar
            'to_user' => 4,   // Aïda
            'montant' => 10000,
            'note' => 'Cadeau'
        ],

        // Paiements marchands
        [
            'type' => 'paiement',
            'from_user' => 0, // Fatou
            'marchand' => 'SDE (Sénégalaise des Eaux)',
            'categorie' => 'Services Publics',
            'montant' => 25000,
            'note' => 'Facture eau'
        ],
        [
            'type' => 'paiement',
            'from_user' => 1, // Abdou
            'marchand' => 'SENELEC',
            'categorie' => 'Énergie',
            'montant' => 35000,
            'note' => 'Facture électricité'
        ],
        [
            'type' => 'paiement',
            'from_user' => 2, // Marie
            'marchand' => 'Orange Money',
            'categorie' => 'Télécommunications',
            'montant' => 5000,
            'note' => 'Recharge'
        ],
        [
            'type' => 'paiement',
            'from_user' => 3, // Omar
            'marchand' => 'Carrefour Market',
            'categorie' => 'Supermarché',
            'montant' => 45000,
            'note' => 'Courses hebdomadaires'
        ],
        [
            'type' => 'paiement',
            'from_user' => 4, // Aïda
            'marchand' => 'Station Total',
            'categorie' => 'Carburant',
            'montant' => 20000,
            'note' => 'Essence'
        ]
    ];

    foreach ($transactions as $index => $tx) {
        $utilisateur = $utilisateurs[$tx['from_user']];
        $portefeuille = $utilisateur->portefeuille;

        // Calculer les frais
        $frais = $tx['type'] === 'transfert' ? calculerFraisTransfert($tx['montant']) : 0;

        // Créer la transaction
        $transaction = new Transaction();
        $transaction->id = \Illuminate\Support\Str::uuid();
        $transaction->id_utilisateur = $utilisateur->id;
        $transaction->type = $tx['type'];
        $transaction->montant = $tx['montant'];
        $transaction->devise = 'XOF';
        $transaction->statut = 'termine';
        $transaction->frais = $frais;
        $transaction->reference = 'TXN' . strtoupper(uniqid()) . time();
        $transaction->date_transaction = now()->subDays(rand(0, 30));

        // Champs spécifiques
        $transaction->numero_telephone_destinataire = $tx['type'] === 'transfert' ? $utilisateurs[$tx['to_user']]->numero_telephone : null;
        $transaction->nom_destinataire = $tx['type'] === 'transfert' ? $utilisateurs[$tx['to_user']]->prenom . ' ' . $utilisateurs[$tx['to_user']]->nom : null;
        $transaction->nom_marchand = $tx['type'] === 'paiement' ? $tx['marchand'] : null;
        $transaction->categorie_marchand = $tx['type'] === 'paiement' ? $tx['categorie'] : null;
        $transaction->note = $tx['note'];

        $transaction->save();

        // Mettre à jour le solde du portefeuille
        if ($tx['type'] === 'transfert') {
            $portefeuille->decrement('solde', $tx['montant'] + $frais);
            // Créditer le destinataire
            $destinataire = $utilisateurs[$tx['to_user']];
            $destinataire->portefeuille->increment('solde', $tx['montant']);
        } else {
            $portefeuille->decrement('solde', $tx['montant']);
        }

        echo "✓ Transaction #{$index}: {$tx['type']} de {$utilisateur->prenom} {$utilisateur->nom} - " . number_format($tx['montant'], 0, ',', ' ') . " FCFA\n";
    }

    echo "\n=== RÉSUMÉ ===\n";
    echo "Utilisateurs créés: " . count($utilisateurs) . "\n";
    echo "Transactions créées: " . count($transactions) . "\n";

    // Afficher les soldes finaux
    echo "\nSoldes finaux:\n";
    foreach ($utilisateurs as $user) {
        $solde = $user->portefeuille->solde;
        echo "- {$user->prenom} {$user->nom}: " . number_format($solde, 0, ',', ' ') . " FCFA\n";
    }

    echo "\n✅ Données de test créées avec succès!\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

function calculerFraisTransfert($montant) {
    if ($montant <= 5000) return 0;
    if ($montant <= 25000) return 0;
    if ($montant <= 50000) return 0;
    if ($montant <= 100000) return 100;
    return 200;
}