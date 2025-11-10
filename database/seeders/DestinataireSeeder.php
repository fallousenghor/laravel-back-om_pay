<?php

namespace Database\Seeders;

use App\Models\Destinataire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DestinataireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des destinataires de test
        $destinataires = [
            [
                'numero_telephone' => '772345678',
                'nom' => 'Mamadou Diallo',
                'operateur' => 'orange',
                'est_valide' => true,
            ],
            [
                'numero_telephone' => '763456789',
                'nom' => 'Fatou Sow',
                'operateur' => 'free',
                'est_valide' => true,
            ],
            [
                'numero_telephone' => '701234567',
                'nom' => 'Ibrahima Ndiaye',
                'operateur' => 'expresso',
                'est_valide' => true,
            ],
            [
                'numero_telephone' => '752345678',
                'nom' => 'Aminata Ba',
                'operateur' => 'expresso',
                'est_valide' => true,
            ],
            [
                'numero_telephone' => '781234567',
                'nom' => 'Cheikh Diop',
                'operateur' => 'orange',
                'est_valide' => false, // Numéro invalide pour test
            ],
        ];

        foreach ($destinataires as $destinataireData) {
            Destinataire::create($destinataireData);
        }

        // Créer des destinataires supplémentaires
        for ($i = 0; $i < 25; $i++) {
            $operateurs = ['77', '78', '76', '80', '75', '70'];
            $operateur = $operateurs[array_rand($operateurs)];
            $numeroTelephone = $operateur . rand(1000000, 9999999);

            Destinataire::create([
                'numero_telephone' => $numeroTelephone,
                'nom' => $this->getRandomPrenom() . ' ' . $this->getRandomNom(),
                'operateur' => $this->determinerOperateur($numeroTelephone),
                'est_valide' => rand(0, 1),
            ]);
        }
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

    private function determinerOperateur(string $numero): string
    {
        $prefixe = substr($numero, 0, 2);
        return match ($prefixe) {
            '77', '78' => 'orange',
            '76', '80' => 'free',
            '70', '75' => 'expresso',
            default => 'autre',
        };
    }
}
