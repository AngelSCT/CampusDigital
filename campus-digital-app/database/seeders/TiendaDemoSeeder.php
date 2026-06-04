<?php

namespace Database\Seeders;

use App\Models\Tienda;
use Illuminate\Database\Seeder;

class TiendaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tiendas = [
            [
                'nombre'      => 'Cafetería Central',
                'tipo'        => 'cafeteria',
                'descripcion' => 'Desayunos, comidas y bebidas para toda la comunidad universitaria.',
                'activo'      => true,
                'color'       => '#f59e0b',
            ],
            [
                'nombre'      => 'Papelería & Copias',
                'tipo'        => 'papeleria',
                'descripcion' => 'Impresiones, copias, encuadernados y artículos de papelería.',
                'activo'      => true,
                'color'       => '#3b82f6',
            ],
            [
                'nombre'      => 'Mercadito Universitario',
                'tipo'        => 'mercadito',
                'descripcion' => 'Espacio para emprendedores y pequeños negocios del campus.',
                'activo'      => true,
                'color'       => '#10b981',
            ],
            [
                'nombre'      => 'Kermesse Navideña 2025',
                'tipo'        => 'kermesse',
                'descripcion' => 'Evento especial con puestos de comida, artesanías y juegos.',
                'activo'      => true,
                'color'       => '#ec4899',
            ],
            [
                'nombre'      => 'Tienda del Estudiante',
                'tipo'        => 'estudiante',
                'descripcion' => 'Vendedores alumnos que ofrecen productos y servicios en el campus.',
                'activo'      => true,
                'color'       => '#8b5cf6',
            ],
        ];

        foreach ($tiendas as $data) {
            Tienda::firstOrCreate(['nombre' => $data['nombre']], $data);
        }

        $this->command->info('✅ Tiendas demo creadas correctamente.');
    }
}
