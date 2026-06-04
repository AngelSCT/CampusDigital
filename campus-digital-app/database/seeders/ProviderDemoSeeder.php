<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear productos de ejemplo para Cafetería
        $productosCafeteria = [
            [
                'nombre' => 'Café Americano',
                'descripcion' => 'Café de grano recién molido, 12oz.',
                'precio' => 35.00,
                'stock' => 50,
                'modulo' => 'cafeteria',
                'activo' => true,
            ],
            [
                'nombre' => 'Chilaquiles Verdes',
                'descripcion' => 'Con pollo, crema, queso y cebolla.',
                'precio' => 75.00,
                'stock' => 20,
                'modulo' => 'cafeteria',
                'activo' => true,
            ],
            [
                'nombre' => 'Sándwich de Jamón',
                'descripcion' => 'Pan integral, jamón de pavo, lechuga y tomate.',
                'precio' => 45.00,
                'stock' => 15,
                'modulo' => 'cafeteria',
                'activo' => true,
            ],
        ];

        foreach ($productosCafeteria as $prod) {
            Producto::updateOrCreate(['nombre' => $prod['nombre'], 'modulo' => $prod['modulo']], $prod);
        }

        // 2. Crear productos para Souvenirs
        $productosSouvenirs = [
            [
                'nombre' => 'Sudadera Universitaria',
                'descripcion' => 'Sudadera azul marino con logo bordado. Talla M.',
                'precio' => 450.00,
                'stock' => 10,
                'modulo' => 'souvenirs',
                'activo' => true,
            ],
            [
                'nombre' => 'Termo Metálico',
                'descripcion' => 'Acero inoxidable, mantiene calor por 12hrs.',
                'precio' => 280.00,
                'stock' => 30,
                'modulo' => 'souvenirs',
                'activo' => true,
            ],
        ];

        foreach ($productosSouvenirs as $prod) {
            Producto::updateOrCreate(['nombre' => $prod['nombre'], 'modulo' => $prod['modulo']], $prod);
        }

        // 3. Crear algunos pedidos de prueba para que el dashboard y reportes tengan datos
        $usuario = Usuario::first(); // Asumimos que existe al menos un usuario por el seeder anterior
        
        if ($usuario) {
            $pedidos = [
                // Pedido entregado ayer (para historial)
                [
                    'usuario_id' => $usuario->id,
                    'numero_folio' => 'DEMO-' . now()->subDay()->timestamp . '-1',
                    'estado' => 'entregado',
                    'modulo' => 'cafeteria',
                    'total' => 110.00,
                    'descripcion' => '1 Café Americano, 1 Chilaquiles',
                    'notas' => 'Sin cebolla',
                    'meta_json' => json_encode(['demo' => true]),
                    'confirmado_at' => now()->subDay()->setHour(9),
                    'created_at' => now()->subDay()->setHour(8)->setMinute(45),
                ],
                // Pedido entregado hoy (mañana)
                [
                    'usuario_id' => $usuario->id,
                    'numero_folio' => 'DEMO-' . now()->timestamp . '-2',
                    'estado' => 'entregado',
                    'modulo' => 'cafeteria',
                    'total' => 35.00,
                    'descripcion' => '1 Café Americano',
                    'meta_json' => json_encode(['demo' => true]),
                    'confirmado_at' => now()->subHours(2),
                    'created_at' => now()->subHours(3),
                ],
                // Pedido pendiente (para el panel operativo)
                [
                    'usuario_id' => $usuario->id,
                    'numero_folio' => 'DEMO-' . now()->timestamp . '-3',
                    'estado' => 'creado',
                    'modulo' => 'cafeteria',
                    'total' => 45.00,
                    'descripcion' => '1 Sándwich de Jamón',
                    'meta_json' => json_encode(['demo' => true]),
                    'created_at' => now()->subMinutes(10),
                ],
                // Pedido en proceso
                [
                    'usuario_id' => $usuario->id,
                    'numero_folio' => 'DEMO-' . now()->timestamp . '-4',
                    'estado' => 'en_proceso',
                    'modulo' => 'souvenirs',
                    'total' => 450.00,
                    'descripcion' => '1 Sudadera Universitaria',
                    'meta_json' => json_encode(['demo' => true]),
                    'created_at' => now()->subMinutes(20),
                ],
            ];

            foreach ($pedidos as $p) {
                // Usar DB insert para evitar eventos de modelo si causan problemas con folios
                DB::table('pedido')->insert($p);
            }
        }
    }
}
