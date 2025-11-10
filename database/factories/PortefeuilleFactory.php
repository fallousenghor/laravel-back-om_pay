<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Portefeuille>
 */
class PortefeuilleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'id_utilisateur' => \App\Models\Utilisateur::factory(),
            'solde' => $this->faker->randomFloat(2, 0, 100000), // Solde entre 0 et 100,000 XOF
            'devise' => 'XOF',
            'derniere_mise_a_jour' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function avecSolde(float $solde): static
    {
        return $this->state(fn (array $attributes) => [
            'solde' => $solde,
        ]);
    }

    public function vide(): static
    {
        return $this->state(fn (array $attributes) => [
            'solde' => 0,
        ]);
    }

    public function riche(): static
    {
        return $this->state(fn (array $attributes) => [
            'solde' => $this->faker->randomFloat(2, 50000, 500000),
        ]);
    }
}
