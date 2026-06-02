<?php

namespace Database\Factories\Catalogo;

use Illuminate\Database\Eloquent\Factories\Factory;

class CatalogoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->words(3, true),
            'descripcion' => $this->faker->sentence,
            'tipo' => 'producto',
            'activo' => true,
            'aplica_iva' => false,
            'fecha_creacion' => now(),
        ];
    }
}
