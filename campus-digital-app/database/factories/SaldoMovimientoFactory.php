<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Modulo;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaldoMovimiento>
 */
class SaldoMovimientoFactory extends Factory
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
            'modulo_id' => Modulo::factory(),
            'tipo' => fake()->randomElement(['cargo', 'abono']),
            'monto' => fake()->numberBetween(50, 500),
            'descripcion' => fake()->sentence(),
            'referencia_transaccional' => fake()->uuid(),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
