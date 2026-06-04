<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

/**
 * Crea el rol 'admin_carrito' necesario para el panel de administración del módulo Carrito.
 * Es idempotente: usa firstOrCreate para no duplicar.
 */
class RolesCartSeeder extends Seeder
{
    public function run(): void
    {
        Rol::firstOrCreate(
            ['nombre' => 'admin_carrito'],
            [
                'descripcion' => 'Administrador del módulo Carrito: aprueba/rechaza solicitudes, gestiona tokens.',
                'activo'      => true,
            ]
        );
    }
}
