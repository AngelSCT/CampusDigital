<?php

namespace Database\Seeders;

use App\Models\CarritoItem;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class CarritoSeeder extends Seeder
{
    public function run(): void
    {
        // Busca 'Juan Pérez' primero; si no existe, toma el primer estudiante disponible.
        $usuario = Usuario::where('nombre', 'Juan')->where('apellido', 'Pérez')->first()
            ?? Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'estudiante'))->first()
            ?? Usuario::first();

        if (! $usuario) {
            $this->command->warn('CarritoSeeder: no hay usuarios en la BD. Ejecuta primero UsuariosPruebaSeeder.');
            return;
        }

        // Productos de prueba para la vista multitienda (deben existir en ProductSeeder)
        $productos = [
            // Cafetería — coincide con tienda del Proveedor Cafetería
            ['nombre' => 'Café Americano',        'cantidad' => 2, 'guardado' => false],
            // Otras tiendas
            ['nombre' => 'Whopper Doble',          'cantidad' => 1, 'guardado' => false],
            ['nombre' => 'Cuaderno Profesional',   'cantidad' => 1, 'guardado' => false],
            // Souvenir — marcado como regalo en pruebas
            ['nombre' => 'Sudadera Campus Digital', 'cantidad' => 1, 'guardado' => false],
        ];

        $asignados = 0;

        foreach ($productos as $datos) {
            $producto = Producto::where('nombre', $datos['nombre'])->first();

            if (! $producto) {
                $this->command->warn("CarritoSeeder: producto '{$datos['nombre']}' no encontrado. Ejecuta primero ProductSeeder.");
                continue;
            }

            $guardado = $datos['guardado'] ?? false;

            CarritoItem::updateOrCreate(
                [
                    'usuario_id'            => $usuario->id,
                    'producto_id'           => $producto->id,
                    'guardado_para_despues' => $guardado,
                    'en_wishlist'           => false,
                ],
                [
                    'cantidad'            => $datos['cantidad'],
                    'ultima_actividad_at' => now(),
                ]
            );

            $asignados++;
        }

        $this->command->info(
            "CarritoSeeder: {$asignados} ítem(s) activos asignados a {$usuario->nombre} {$usuario->apellido} (ID: {$usuario->id})."
        );
    }
}
