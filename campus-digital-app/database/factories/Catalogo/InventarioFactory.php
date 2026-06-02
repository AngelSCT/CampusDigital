<?php

namespace Database\Factories\Catalogo;

use Illuminate\Database\Eloquent\Factories\Factory;

class InventarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'stock_actual' => 10,
            'fecha_actualizacion' => now(),
        ];
    }
}
