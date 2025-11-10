<?php

namespace Database\Seeders;

use App\Models\Portefeuille;
use App\Models\User;
use Illuminate\Database\Seeder;

class PortefeuilleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\Utilisateur::all();

        foreach ($users as $user) {
            // Vérifie si l'utilisateur a déjà un portefeuille
            if (!Portefeuille::where('id_utilisateur', $user->id)->exists()) {
                Portefeuille::factory()->create([
                    'id_utilisateur' => $user->id,
                    'solde' => mt_rand(10000, 1000000), // Solde aléatoire entre 10,000 et 1,000,000
                    'devise' => 'XOF'
                ]);
            }
        }
    }
}