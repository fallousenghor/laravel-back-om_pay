<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Test simple d'insertion directe
        DB::table('utilisateurs')->insert([
            'numero_telephone' => '771234567',
            'prenom' => 'Test',
            'nom' => 'User',
            'email' => 'test@example.com',
            'code_pin' => bcrypt('1234'),
            'numero_cni' => '123456789012',
            'statut_kyc' => 'verifie',
            'biometrie_activee' => false,
            'date_creation' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo __('messages.fr.seeders.test_insertion_success') . "\n";
    }
}

