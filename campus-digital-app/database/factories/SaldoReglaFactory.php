<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Modulo;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaldoRegla>
 */
class SaldoReglaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'tipo_limite' => fake()->randomElement(['diario', 'semanal', 'mensual']),
            'monto_maximo' => fake()->numberBetween(500, 5000),
            'modulo_id' => Modulo::factory(),
            'descripcion' => fake()->sentence(),
        ];
    }
}
