<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [

            // ── CAFETERÍA — Bebidas ──────────────────────────────────────────
            [
                'nombre'             => 'Café Americano',
                'precio'             => 25.00,
                'stock'              => 150,
                'categoria'          => 'Bebidas',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 5,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Café con Leche',
                'precio'             => 30.00,
                'stock'              => 120,
                'categoria'          => 'Bebidas',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 5,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Agua Natural 600 ml',
                'precio'             => 15.00,
                'stock'              => 200,
                'categoria'          => 'Bebidas',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 10,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],

            // ── CAFETERÍA — Alimentos ────────────────────────────────────────
            [
                'nombre'             => 'Torta de Jamón',
                'precio'             => 45.00,
                'stock'              => 80,
                'categoria'          => 'Alimentos',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 3,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Torta Cubana',
                'precio'             => 65.00,
                'stock'              => 60,
                'categoria'          => 'Alimentos',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 3,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Yogurt con Granola',
                'precio'             => 38.00,
                'stock'              => 50,
                'categoria'          => 'Alimentos',
                'tienda'             => 'Cafetería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 5,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],

            // ── LIBRERÍA — Papelería ─────────────────────────────────────────
            [
                'nombre'             => 'Libreta Universitaria 100 hojas',
                'precio'             => 55.00,
                'stock'              => 75,
                'categoria'          => 'Papelería',
                'tienda'             => 'Librería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 5,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Libreta de Espiral Profesional',
                'precio'             => 75.00,
                'stock'              => 50,
                'categoria'          => 'Papelería',
                'tienda'             => 'Librería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 5,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Bolígrafos (paquete 3)',
                'precio'             => 35.00,
                'stock'              => 100,
                'categoria'          => 'Papelería',
                'tienda'             => 'Librería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 10,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],

            // ── LIBRERÍA — Tecnología ────────────────────────────────────────
            [
                'nombre'             => 'Memoria USB 32 GB',
                'precio'             => 185.00,
                'stock'              => 30,
                'categoria'          => 'Tecnología',
                'tienda'             => 'Librería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 2,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],
            [
                'nombre'             => 'Memoria USB 64 GB',
                'precio'             => 280.00,
                'stock'              => 20,
                'categoria'          => 'Tecnología',
                'tienda'             => 'Librería',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 2,
                'imagen_url'         => null,
                'es_regalo'          => false,
            ],

            // ── SOUVENIRS — Ropa institucional ───────────────────────────────
            [
                'nombre'             => 'Sudadera del Instituto',
                'precio'             => 380.00,
                'stock'              => 100,
                'categoria'          => 'Ropa',
                'tienda'             => 'Souvenirs',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 3,
                'imagen_url'         => null,
                'es_regalo'          => true,
            ],
            [
                'nombre'             => 'Playera Institucional',
                'precio'             => 220.00,
                'stock'              => 80,
                'categoria'          => 'Ropa',
                'tienda'             => 'Souvenirs',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 3,
                'imagen_url'         => null,
                'es_regalo'          => true,
            ],
            [
                'nombre'             => 'Gorra Campus Digital',
                'precio'             => 185.00,
                'stock'              => 60,
                'categoria'          => 'Accesorios',
                'tienda'             => 'Souvenirs',
                'tipo'               => 'producto',
                'fecha_inicio_evento'=> null,
                'limite_por_usuario' => 3,
                'imagen_url'         => null,
                'es_regalo'          => true,
            ],

            // ── EVENTOS ──────────────────────────────────────────────────────
            // tipo='evento': expiracion de regalo = 1h antes de fecha_inicio_evento
            [
                'nombre'             => "Boleto Conferencia TechDay 2026",
                'precio'             => 150.00,
                'stock'              => 200,
                'categoria'          => 'Eventos',
                'tienda'             => 'Eventos',
                'tipo'               => 'evento',
                'fecha_inicio_evento'=> '2026-05-28 10:00:00',
                'limite_por_usuario' => 2,
                'imagen_url'         => null,
                'es_regalo'          => true,
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
