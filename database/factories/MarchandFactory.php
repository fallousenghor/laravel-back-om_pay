<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Marchand>
 */
class MarchandFactory extends Factory
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
            'nom' => $this->faker->company(),
            'numero_telephone' => $numeroTelephone,
            'adresse' => $this->faker->address(),
            'logo' => $this->faker->imageUrl(200, 200, 'business'),
            'actif' => $this->faker->boolean(90), // 90% de chance d'être actif
            'accepte_qr' => $this->faker->boolean(80),
            'accepte_code' => $this->faker->boolean(85),
        ];
    }

    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'actif' => true,
        ]);
    }

    public function accepteQR(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepte_qr' => true,
        ]);
    }

    public function accepteCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepte_code' => true,
        ]);
    }

    public function boutique(): static
    {
        return $this->state(fn (array $attributes) => [
            'nom' => $this->faker->company() . ' Boutique',
        ]);
    }
}
