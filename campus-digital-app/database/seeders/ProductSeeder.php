<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // ── STARBUCKS ────────────────────────────────────────────────────
            [
                'nombre'             => 'Café Americano',
                'precio'             => 45.00,
                'stock'              => 100,
                'categoria'          => 'Bebidas',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'limite_por_usuario' => 3,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Frappuccino Chip',
                'precio'             => 85.00,
                'stock'              => 40,
                'categoria'          => 'Bebidas',
                'tienda'             => 'Starbucks',
                'tipo'               => 'producto',
                'limite_por_usuario' => 3,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Muffins',
                'precio'             => 40.00,
                'stock'              => 30,
                'categoria'          => 'Repostería',
                'tienda'             => 'Starbucks',
                'tipo'               => 'producto',
                'limite_por_usuario' => 5,
                'es_regalo'          => false,
            ],

            // ── CAFETERÍA ────────────────────────────────────────────────────
            [
                'nombre'             => 'Café del Día',
                'precio'             => 25.00,
                'stock'              => 150,
                'categoria'          => 'Bebidas',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'limite_por_usuario' => 5,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Sándwich Integral',
                'precio'             => 55.00,
                'stock'              => 60,
                'categoria'          => 'Alimentos',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'limite_por_usuario' => 3,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Yogurt con Granola',
                'precio'             => 40.00,
                'stock'              => 40,
                'categoria'          => 'Alimentos',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'limite_por_usuario' => 5,
                'es_regalo'          => false,
            ],

            // ── BURGER KING ──────────────────────────────────────────────────
            [
                'nombre'             => 'Whopper Doble',
                'precio'             => 120.00,
                'stock'              => 60,
                'categoria'          => 'Comida',
                'tienda'             => 'Burger King',
                'tipo'               => 'producto',
                'limite_por_usuario' => 3,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Papas Medianas',
                'precio'             => 55.00,
                'stock'              => 80,
                'categoria'          => 'Acompañamientos',
                'tienda'             => 'Burger King',
                'tipo'               => 'producto',
                'limite_por_usuario' => 5,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Cono Sencillo',
                'precio'             => 15.00,
                'stock'              => 100,
                'categoria'          => 'Postres',
                'tienda'             => 'Burger King',
                'tipo'               => 'producto',
                'limite_por_usuario' => 5,
                'es_regalo'          => false,
            ],

            // ── LIBRERÍA ─────────────────────────────────────────────────────
            [
                'nombre'             => 'Cuaderno Profesional',
                'precio'             => 95.00,
                'stock'              => 25,
                'categoria'          => 'Papelería',
                'tienda'             => 'Librería',
                'tipo'               => 'producto',
                'limite_por_usuario' => 5,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Kit de Plumas',
                'precio'             => 60.00,
                'stock'              => 35,
                'categoria'          => 'Papelería',
                'tienda'             => 'Librería',
                'tipo'               => 'producto',
                'limite_por_usuario' => 5,
                'es_regalo'          => false,
            ],

            // ── SOUVENIRS ────────────────────────────────────────────────────
            [
                'nombre'             => 'Sudadera Campus Digital',
                'precio'             => 350.00,
                'stock'              => 100,
                'categoria'          => 'Ropa',
                'tienda'             => 'Souvenirs',
                'tipo'               => 'producto',
                'limite_por_usuario' => 3,
                'es_regalo'          => true,
            ],
            [
                'nombre'             => 'Gorra Campus',
                'precio'             => 180.00,
                'stock'              => 50,
                'categoria'          => 'Accesorios',
                'tienda'             => 'Souvenirs',
                'tipo'               => 'producto',
                'limite_por_usuario' => 3,
                'es_regalo'          => true,
            ],

            // ── EVENTOS ──────────────────────────────────────────────────────
            // tipo='evento': la expiración del regalo se calcula 1 hora antes
            // de fecha_inicio_evento (via CartController::calcularExpiracionRegalo)
            [
                'nombre'               => "Boleto 'Fiesta de Bienvenida'",
                'precio'               => 200.00,
                'stock'                => 150,
                'categoria'            => 'Eventos',
                'tienda'               => 'Eventos',
                'tipo'                 => 'evento',
                'fecha_inicio_evento'  => '2026-05-20 18:00:00',
                'limite_por_usuario'   => 2,
                'es_regalo'            => true,
            ],
            [
                'nombre'             => 'Impresiones (10 págs.)',
                'precio'             => 12.00,
                'stock'              => 200,
                'categoria'          => 'Servicios',
                'tienda'             => 'General',
                'tipo'               => 'producto',
                'limite_por_usuario' => 10,
                'es_regalo'          => false,
            ],
        ];

        foreach ($productos as $data) {
            Producto::updateOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}
