<?php

namespace Database\Seeders;

use App\Models\Marchand;
use App\Models\QRCode;
use App\Models\CodePaiement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarchandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des marchands de test
        $marchands = [
            [
                'nom' => 'Boutique Orange',
                'numero_telephone' => '772345678',
                'adresse' => 'Plateau Dakar',
                'accepte_qr' => true,
                'accepte_code' => true,
            ],
            [
                'nom' => 'Pharmacie Centrale',
                'numero_telephone' => '763456789',
                'adresse' => 'Mermoz Dakar',
                'accepte_qr' => true,
                'accepte_code' => false,
            ],
            [
                'nom' => 'Restaurant Le Jardin',
                'numero_telephone' => '701234567',
                'adresse' => 'Ouakam Dakar',
                'accepte_qr' => false,
                'accepte_code' => true,
            ],
            [
                'nom' => 'Station Service Total',
                'numero_telephone' => '752345678',
                'adresse' => 'Route de Ouakam',
                'accepte_qr' => true,
                'accepte_code' => true,
            ],
            [
                'nom' => 'Supermarché Casino',
                'numero_telephone' => '781234567',
                'adresse' => 'Almadies Dakar',
                'accepte_qr' => true,
                'accepte_code' => true,
            ],
        ];

        foreach ($marchands as $marchandData) {
            $marchand = Marchand::create($marchandData);

            // Créer quelques QR codes pour les marchands qui les acceptent
            if ($marchandData['accepte_qr']) {
                for ($i = 0; $i < rand(1, 3); $i++) {
                        QRCode::create([
                            'id_marchand' => $marchand->id,
                            'donnees' => 'QR_' . strtoupper(Str::random(16)),
                            'montant' => rand(1000, 50000),
                            'date_generation' => now(),
                            'date_expiration' => now()->addMinutes(rand(15, 60)),
                            'utilise' => false,
                        ]);
                }
            }

            // Créer quelques codes de paiement pour les marchands qui les acceptent
            if ($marchandData['accepte_code']) {
                for ($i = 0; $i < rand(2, 5); $i++) {
                        CodePaiement::create([
                            'code' => strtoupper(Str::random(6)),
                            'id_marchand' => $marchand->id,
                            'montant' => rand(500, 25000),
                            'date_generation' => now(),
                            'date_expiration' => now()->addMinutes(rand(10, 30)),
                            'utilise' => false,
                        ]);
                }
            }
        }

        // Créer des marchands supplémentaires
        for ($i = 0; $i < 15; $i++) {
            $accepteQr = rand(0, 1);
            $accepteCode = rand(0, 1);

                $marchand = Marchand::create([
                'nom' => $this->getRandomMarchandName(),
                'numero_telephone' => '77' . rand(1000000, 9999999),
                'adresse' => $this->getRandomAdresse(),
                'logo' => rand(0, 1) ? 'https://via.placeholder.com/100' : null,
                'actif' => true,
                'accepte_qr' => $accepteQr,
                    'accepte_code' => $accepteCode,
            ]);

            // Créer des QR codes
            if ($accepteQr) {
                for ($j = 0; $j < rand(1, 3); $j++) {
                        QRCode::create([
                            'id_marchand' => $marchand->id,
                            'donnees' => 'QR_' . strtoupper(Str::random(16)),
                            'montant' => rand(1000, 50000),
                            'date_generation' => now(),
                            'date_expiration' => now()->addMinutes(rand(15, 60)),
                            'utilise' => false,
                        ]);
                }
            }

            // Créer des codes de paiement
            if ($accepteCode) {
                for ($j = 0; $j < rand(1, 4); $j++) {
                        CodePaiement::create([
                            'code' => strtoupper(Str::random(6)),
                            'id_marchand' => $marchand->id,
                            'montant' => rand(500, 25000),
                            'date_generation' => now(),
                            'date_expiration' => now()->addMinutes(rand(10, 30)),
                            'utilise' => false,
                        ]);
                }
            }
        }
    }

    private function getRandomMarchandName(): string
    {
        $noms = [
            'Boutique Express', 'Pharmacie Centrale', 'Restaurant Gourmet', 'Station Service Plus',
            'Supermarché Fresh', 'Café Central', 'Boulangerie Tradition', 'Épicerie Moderne',
            'Magasin Électronique', 'Librairie Culture', 'Garage Auto', 'Salon Coiffure'
        ];
        return $noms[array_rand($noms)];
    }

    private function getRandomAdresse(): string
    {
        $adresses = [
            'Plateau Dakar', 'Mermoz Dakar', 'Ouakam Dakar', 'Almadies Dakar',
            'Route de Ouakam', 'Centre Ville', 'Mermoz Nord', 'Plateau Centre'
        ];
        return $adresses[array_rand($adresses)];
    }
}
