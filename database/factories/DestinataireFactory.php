<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Destinataire>
 */
class DestinataireFactory extends Factory
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
            'nom' => $this->faker->name(),
            'operateur' => $this->determinerOperateur($numeroTelephone),
            'est_valide' => $this->faker->boolean(95), // 95% de chance d'être valide
        ];
    }

    public function valide(): static
    {
        return $this->state(fn (array $attributes) => [
            'est_valide' => true,
        ]);
    }

    public function orange(): static
    {
        return $this->state(function (array $attributes) {
            $numero = '77' . $this->faker->numberBetween(1000000, 9999999);
            return [
                'numero_telephone' => $numero,
                'operateur' => 'orange',
            ];
        });
    }

    public function free(): static
    {
        return $this->state(function (array $attributes) {
            $numero = '76' . $this->faker->numberBetween(1000000, 9999999);
            return [
                'numero_telephone' => $numero,
                'operateur' => 'free',
            ];
        });
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
