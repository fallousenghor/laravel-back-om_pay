<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
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
            'id_utilisateur' => \App\Models\Utilisateur::factory(),
            'nom' => $this->faker->name(),
            'numero_telephone' => $numeroTelephone,
            'photo' => $this->faker->optional(0.3)->imageUrl(100, 100, 'people'), // 30% de chance d'avoir une photo
            'nombre_transactions' => $this->faker->numberBetween(0, 50),
            'derniere_transaction' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'), // 80% de chance d'avoir une dernière transaction
        ];
    }

    public function frequent(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre_transactions' => $this->faker->numberBetween(20, 100),
            'derniere_transaction' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'derniere_transaction' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    public function avecPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo' => $this->faker->imageUrl(100, 100, 'people'),
        ]);
    }
}
