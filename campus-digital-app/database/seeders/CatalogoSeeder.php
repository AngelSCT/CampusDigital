<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * CatalogoSeeder — Módulo 4.3 (Catálogo de Servicios y Productos)
 *
 * Carga datos de prueba para facilitar la integración con el Módulo 4.5.
 * Incluye categorías, impuesto IVA, productos y servicios del campus,
 * y precios vigentes para cada ítem.
 *
 * Uso:
 *   php artisan db:seed --class=CatalogoSeeder
 *
 * También puedes agregarlo al DatabaseSeeder:
 *   $this->call(CatalogoSeeder::class);
 */
class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando CatalogoSeeder...');

        // ── 1. Categorías ────────────────────────────────────────────────────
        $categorias = [
            ['nombre' => 'Cafetería',      'descripcion' => 'Alimentos y bebidas del campus',          'activo' => true],
            ['nombre' => 'Copias e Impresiones', 'descripcion' => 'Servicios de copiado e impresión', 'activo' => true],
            ['nombre' => 'Trámites',       'descripcion' => 'Servicios administrativos y trámites',    'activo' => true],
            ['nombre' => 'Souvenirs',      'descripcion' => 'Artículos y recuerdos del campus',        'activo' => true],
            ['nombre' => 'Servicios Internos', 'descripcion' => 'Servicios de uso interno del campus', 'activo' => true],
        ];

        foreach ($categorias as $cat) {
            DB::table('categorias')->insertOrIgnore($cat);
        }

        $idCafeteria      = DB::table('categorias')->where('nombre', 'Cafetería')->value('id_categoria');
        $idCopias         = DB::table('categorias')->where('nombre', 'Copias e Impresiones')->value('id_categoria');
        $idTramites       = DB::table('categorias')->where('nombre', 'Trámites')->value('id_categoria');
        $idSouvenirs      = DB::table('categorias')->where('nombre', 'Souvenirs')->value('id_categoria');
        $idServicios      = DB::table('categorias')->where('nombre', 'Servicios Internos')->value('id_categoria');

        $this->command->info('Categorías: OK');

        // ── 2. Impuesto IVA ──────────────────────────────────────────────────
        $idIva = DB::table('impuestos')->where('nombre', 'IVA')->value('id_impuesto');
        if (!$idIva) {
            $idIva = DB::table('impuestos')->insertGetId([
                'nombre'      => 'IVA',
                'porcentaje'  => 16.00,
                'activo'      => true,
            ], 'id_impuesto');
        }

        $this->command->info('Impuesto IVA: OK');

        // ── 3. Catálogo de productos y servicios ─────────────────────────────
        //   tipo: 'producto' | 'servicio'
        $catalogo = [
            // — Cafetería (productos) —
            [
                'nombre'       => 'Café Americano',
                'descripcion'  => 'Café negro 250 ml',
                'tipo'         => 'producto',
                'id_categoria' => $idCafeteria,
                'aplica_iva'   => false,   // alimentos exentos
                'id_impuesto'  => null,
                'activo'       => true,
                'precio'       => 18.00,
            ],
            [
                'nombre'       => 'Sándwich de Jamón y Queso',
                'descripcion'  => 'Sándwich en pan integral',
                'tipo'         => 'producto',
                'id_categoria' => $idCafeteria,
                'aplica_iva'   => false,
                'id_impuesto'  => null,
                'activo'       => true,
                'precio'       => 45.00,
            ],
            [
                'nombre'       => 'Refresco 600 ml',
                'descripcion'  => 'Bebida carbonatada en botella',
                'tipo'         => 'producto',
                'id_categoria' => $idCafeteria,
                'aplica_iva'   => true,
                'id_impuesto'  => $idIva,
                'activo'       => true,
                'precio'       => 20.00,
            ],
            [
                'nombre'       => 'Enchiladas Verdes',
                'descripcion'  => 'Plato del día: 3 enchiladas con pollo',
                'tipo'         => 'producto',
                'id_categoria' => $idCafeteria,
                'aplica_iva'   => false,
                'id_impuesto'  => null,
                'activo'       => true,
                'precio'       => 65.00,
            ],
            // — Copias e Impresiones (servicios) —
            [
                'nombre'       => 'Copia Simple B/N',
                'descripcion'  => 'Copia blanco y negro tamaño carta',
                'tipo'         => 'servicio',
                'id_categoria' => $idCopias,
                'aplica_iva'   => true,
                'id_impuesto'  => $idIva,
                'activo'       => true,
                'precio'       => 1.50,
            ],
            [
                'nombre'       => 'Impresión a Color',
                'descripcion'  => 'Impresión a color tamaño carta',
                'tipo'         => 'servicio',
                'id_categoria' => $idCopias,
                'aplica_iva'   => true,
                'id_impuesto'  => $idIva,
                'activo'       => true,
                'precio'       => 5.00,
            ],
            [
                'nombre'       => 'Engargolado',
                'descripcion'  => 'Engargolado con portada hasta 100 hojas',
                'tipo'         => 'servicio',
                'id_categoria' => $idCopias,
                'aplica_iva'   => true,
                'id_impuesto'  => $idIva,
                'activo'       => true,
                'precio'       => 35.00,
            ],
            // — Trámites (servicios) —
            [
                'nombre'       => 'Constancia de Estudios',
                'descripcion'  => 'Constancia oficial con sello institucional',
                'tipo'         => 'servicio',
                'id_categoria' => $idTramites,
                'aplica_iva'   => false,
                'id_impuesto'  => null,
                'activo'       => true,
                'precio'       => 50.00,
            ],
            [
                'nombre'       => 'Certificado de Calificaciones',
                'descripcion'  => 'Certificado oficial por semestre',
                'tipo'         => 'servicio',
                'id_categoria' => $idTramites,
                'aplica_iva'   => false,
                'id_impuesto'  => null,
                'activo'       => true,
                'precio'       => 80.00,
            ],
            // — Souvenirs (productos) —
            [
                'nombre'       => 'Taza del Campus',
                'descripcion'  => 'Taza cerámica con logotipo institucional',
                'tipo'         => 'producto',
                'id_categoria' => $idSouvenirs,
                'aplica_iva'   => true,
                'id_impuesto'  => $idIva,
                'activo'       => true,
                'precio'       => 120.00,
            ],
            [
                'nombre'       => 'Camiseta Universitaria',
                'descripcion'  => 'Playera polo talla M con bordado',
                'tipo'         => 'producto',
                'id_categoria' => $idSouvenirs,
                'aplica_iva'   => true,
                'id_impuesto'  => $idIva,
                'activo'       => true,
                'precio'       => 250.00,
            ],
            // — Servicios Internos —
            [
                'nombre'       => 'Uso de Sala de Cómputo',
                'descripcion'  => 'Acceso por hora a sala de cómputo',
                'tipo'         => 'servicio',
                'id_categoria' => $idServicios,
                'aplica_iva'   => false,
                'id_impuesto'  => null,
                'activo'       => true,
                'precio'       => 10.00,
            ],
        ];

        $hoy = now()->toDateString();

        foreach ($catalogo as $item) {
            $precio = $item['precio'];
            unset($item['precio']);

            // Evita duplicados por nombre
            $existente = DB::table('catalogo')->where('nombre', $item['nombre'])->first();
            if ($existente) {
                $idCatalogo = $existente->id_catalogo;
            } else {
                $idCatalogo = DB::table('catalogo')->insertGetId($item, 'id_catalogo');
            }

            // Precio vigente: sólo inserta si no hay uno activo
            $tienePrecioVigente = DB::table('precios')
                ->where('id_catalogo', $idCatalogo)
                ->where('fecha_inicio', '<=', $hoy)
                ->where(function ($q) use ($hoy) {
                    $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $hoy);
                })
                ->exists();

            if (!$tienePrecioVigente) {
                DB::table('precios')->insert([
                    'id_catalogo'  => $idCatalogo,
                    'precio'       => $precio,
                    'fecha_inicio' => '2026-01-01',
                    'fecha_fin'    => null,   // vigente indefinidamente
                ]);
            }
        }

        $total = DB::table('catalogo')->count();
        $totalPrecios = DB::table('precios')->count();

        $this->command->info("CatalogoSeeder completado.");
        $this->command->table(
            ['Recurso', 'Cantidad'],
            [
                ['Categorías',                  DB::table('categorias')->count()],
                ['Impuestos',                   DB::table('impuestos')->count()],
                ['Ítems en catálogo',           $total],
                ['Precios vigentes insertados', $totalPrecios],
            ]
        );
    }
}
