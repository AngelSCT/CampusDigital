<?php

namespace Database\Seeders;

use App\Models\CarritoItem;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class UsuarioCarritoSeeder extends Seeder
{
    public function run(): void
    {
        $usuario = Usuario::where('email', 'estudiante@campusdigital.com')->first();

        if (!$usuario) {
            $this->command->warn('Usuario estudiante@campusdigital.com no encontrado. Ejecuta primero UsuariosPruebaSeeder.');
            return;
        }

        $productos = ['Torta Cubana', 'Café Americano', 'Memoria USB 32 GB'];

        foreach ($productos as $nombre) {
            $producto = Producto::where('nombre', $nombre)->first();

            if (!$producto) {
                $this->command->warn("Producto '{$nombre}' no encontrado. Ejecuta primero ProductSeeder.");
                continue;
            }

            CarritoItem::updateOrCreate(
                [
                    'usuario_id'  => $usuario->id,
                    'producto_id' => $producto->id,
                ],
                [
                    'cantidad'              => 1,
                    'guardado_para_despues' => false,
                    'en_wishlist'           => false,
                    'es_regalo'             => false,
                    'ultima_actividad_at'   => now(),
                ]
            );

            $this->command->info("Agregado al carrito: {$nombre}");
        }

        $this->command->info("Carrito de {$usuario->nombre} {$usuario->apellido} actualizado.");
    }
}
