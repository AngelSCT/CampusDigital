<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Area;
use App\Models\CategoriaTicket;

class CategoriasTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Asegurarse de que exista al menos un área
        $area = Area::firstOrCreate(
            ['name_area' => 'Mantenimiento General']
        );

        // 2. Crear algunas categorías base de tickets
        $categorias = [
            ['nombre_categoria' => 'Reparación de Hardware', 'id_area' => $area->id_area],
            ['nombre_categoria' => 'Instalación de Software', 'id_area' => $area->id_area],
            ['nombre_categoria' => 'Mantenimiento Preventivo', 'id_area' => $area->id_area],
            ['nombre_categoria' => 'Infraestructura', 'id_area' => $area->id_area],
        ];

        foreach ($categorias as $categoria) {
            CategoriaTicket::firstOrCreate(
                ['nombre_categoria' => $categoria['nombre_categoria']],
                $categoria
            );
        }
    }
}
