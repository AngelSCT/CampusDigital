<?php

namespace Database\Factories\Catalogo;

use Illuminate\Database\Eloquent\Factories\Factory;

class PrecioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'precio' => 18.00,
            'fecha_inicio' => now()->subDay(),
        ];
    }
}
