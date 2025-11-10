<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UtilisateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des utilisateurs de test avec insertion directe (compatible PostgreSQL)
        for ($i = 0; $i < 10; $i++) {
            $utilisateurId = DB::table('utilisateurs')->insertGetId([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'numero_telephone' => '77' . rand(1000000, 9999999),
                'prenom' => $this->getRandomPrenom(),
                'nom' => $this->getRandomNom(),
                'email' => 'user' . $i . '@example.com',
                'code_pin' => bcrypt('1234'),
                'numero_cni' => (string) rand(100000000000, 999999999999),
                'statut_kyc' => $this->getRandomKycStatus(),
                'biometrie_activee' => rand(0, 1),
                'date_creation' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Créer le portefeuille avec solde 0 pour les utilisateurs normaux
            DB::table('portefeuilles')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'id_utilisateur' => $utilisateurId,
                'solde' => 0,
                'devise' => 'XOF',
                'derniere_mise_a_jour' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Créer les paramètres de sécurité
            DB::table('parametres_securites')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'id_utilisateur' => $utilisateurId,
                'biometrie_active' => rand(0, 1),
                'tentatives_echouees' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Créer quelques contacts
            $numContacts = rand(3, 8);
            for ($j = 0; $j < $numContacts; $j++) {
                DB::table('contacts')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'id_utilisateur' => $utilisateurId,
                    'nom' => $this->getRandomPrenom() . ' ' . $this->getRandomNom(),
                    'numero_telephone' => '77' . rand(1000000, 9999999),
                    'photo' => rand(0, 1) ? 'https://via.placeholder.com/100' : null,
                    'nombre_transactions' => rand(0, 50),
                    'derniere_transaction' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Créer un utilisateur administrateur
        $adminId = DB::table('utilisateurs')->insertGetId([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'numero_telephone' => '771234567',
            'prenom' => 'Admin',
            'nom' => 'Orange Money',
            'email' => 'admin@orangemoney.sn',
            'code_pin' => bcrypt('1234'),
            'numero_cni' => '123456789012',
            'statut_kyc' => 'verifie',
            'biometrie_activee' => true,
            'date_creation' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('portefeuilles')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'id_utilisateur' => $adminId,
            'solde' => 10000, // 10 000 XOF pour l'utilisateur principal
            'devise' => 'XOF',
            'derniere_mise_a_jour' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('parametres_securites')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'id_utilisateur' => $adminId,
            'biometrie_active' => true,
            'tentatives_echouees' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getRandomPrenom(): string
    {
        $prenoms = ['Mamadou', 'Fatou', 'Ibrahima', 'Aminata', 'Cheikh', 'Ndeye', 'Ousmane', 'Adama', 'Samba', 'Mariama'];
        return $prenoms[array_rand($prenoms)];
    }

    private function getRandomNom(): string
    {
        $noms = ['Diallo', 'Sow', 'Ndiaye', 'Ba', 'Diagne', 'Gueye', 'Fall', 'Sy', 'Kane', 'Mbaye'];
        return $noms[array_rand($noms)];
    }

    private function getRandomKycStatus(): string
    {
        $statuses = ['non_verifie', 'en_cours', 'verifie', 'rejete'];
        return $statuses[array_rand($statuses)];
    }
}
