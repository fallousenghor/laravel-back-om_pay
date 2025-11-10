<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Utilisateur>
 */
class UtilisateurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $operateurs = ['77', '78', '76', '80', '75', '70'];
        $operateur = $this->faker->randomElement($operateurs);
        $numeroTelephone = $operateur . $this->faker->numberBetween(1000000, 9999999);

        return [
            'numero_telephone' => $numeroTelephone,
            'prenom' => $this->faker->firstName(),
            'nom' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'code_pin' => bcrypt('1234'), // Code PIN par défaut pour les tests
            'numero_cni' => $this->faker->unique()->numberBetween(100000000000, 999999999999),
            'statut_kyc' => $this->faker->randomElement(['non_verifie', 'en_cours', 'verifie', 'rejete']),
            'biometrie_activee' => $this->faker->boolean(30), // 30% de chance d'avoir la biométrie activée
            'date_creation' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'derniere_connexion' => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function verifie(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut_kyc' => 'verifie',
        ]);
    }

    public function avecBiometrie(): static
    {
        return $this->state(fn (array $attributes) => [
            'biometrie_activee' => true,
        ]);
    }

    public function orange(): static
    {
        return $this->state(function (array $attributes) {
            $numero = '77' . $this->faker->numberBetween(1000000, 9999999);
            return [
                'numero_telephone' => $numero,
            ];
        });
    }
}
