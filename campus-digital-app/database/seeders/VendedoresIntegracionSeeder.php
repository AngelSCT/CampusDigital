<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tienda;

class VendedoresIntegracionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Iniciando VendedoresIntegracionSeeder...');

        // ── 1. Crear Vendedores oficiales (Módulo 4.3) ───────────────────────
        $vendedores = [
            [
                'id_vendedor' => 3,
                'nombre'      => 'Cafetería Central - Institucional',
                'email'       => 'cafe.institucional@campus.edu',
                'telefono'    => '4491234567',
                'descripcion' => 'Catálogo oficial de alimentos y bebidas del campus',
                'activo'      => true,
            ],
            [
                'id_vendedor' => 4,
                'nombre'      => 'Servicios Escolares & Papelería',
                'email'       => 'papeleria.institucional@campus.edu',
                'telefono'    => '4497654321',
                'descripcion' => 'Servicios oficiales de copias, impresiones y trámites',
                'activo'      => true,
            ]
        ];

        foreach ($vendedores as $v) {
            DB::table('vendedores')->insertOrIgnore($v);
        }
        $this->command->info('Vendedores: OK');

        // Obtener categorías de base
        $idCafeteria = DB::table('categorias')->where('nombre', 'Cafetería')->value('id_categoria');
        $idCopias    = DB::table('categorias')->where('nombre', 'Copias e Impresiones')->value('id_categoria');
        $idTramites  = DB::table('categorias')->where('nombre', 'Trámites')->value('id_categoria');

        // ── 2. Crear Catálogo Personalizado para Vendedor 3 (Cafetería) ──────
        $cafeteriaBaseItems = DB::table('catalogo')->where('id_categoria', $idCafeteria)->get();
        foreach ($cafeteriaBaseItems as $item) {
            // Evitar duplicados
            $idCv = DB::table('catalogo_vendedor')
                ->where('id_vendedor', 3)
                ->where('id_catalogo_base', $item->id_catalogo)
                ->value('id_cv');

            if (!$idCv) {
                $idCv = DB::table('catalogo_vendedor')->insertGetId([
                    'id_vendedor'               => 3,
                    'id_catalogo_base'          => $item->id_catalogo,
                    'nombre_personalizado'      => $item->nombre . ' (Oficial)',
                    'descripcion_personalizada' => $item->descripcion . ' - Preparado al momento.',
                    'tipo'                      => $item->tipo,
                    'id_categoria'              => $item->id_categoria,
                    'activo'                    => true,
                ], 'id_cv');

                // Obtener precio base
                $precioBase = DB::table('precios')->where('id_catalogo', $item->id_catalogo)->value('precio') ?? 20.00;

                // Precio de vendedor (Cafetería Central puede aplicar un ligero descuento o cargo)
                DB::table('precios_vendedor')->insertOrIgnore([
                    'id_cv'        => $idCv,
                    'precio'       => $precioBase, // Mantener precio oficial para consistencia
                    'fecha_inicio' => '2026-01-01',
                    'fecha_fin'    => null,
                ]);

                // Disponibilidad de vendedor (Lunes a Viernes de 7:00 a 20:00)
                $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                foreach ($dias as $dia) {
                    DB::table('disponibilidad_vendedor')->insertOrIgnore([
                        'id_cv'       => $idCv,
                        'dia_semana'  => $dia,
                        'hora_inicio' => '07:00:00',
                        'hora_fin'    => '20:00:00',
                        'disponible'  => true,
                    ]);
                }

                // Regla de negocio del vendedor
                DB::table('reglas_vendedor')->insertOrIgnore([
                    'id_cv'       => $idCv,
                    'descripcion' => 'Límite de compra: Máximo 5 por estudiante',
                    'tipo_regla'  => 'límite_compra',
                ]);
            }
        }
        $this->command->info('Catálogo Vendedor 3 (Cafetería): OK');

        // ── 3. Crear Catálogo Personalizado para Vendedor 4 (Papelería/Trámites) 
        $papeleriaBaseItems = DB::table('catalogo')->whereIn('id_categoria', [$idCopias, $idTramites])->get();
        foreach ($papeleriaBaseItems as $item) {
            $idCv = DB::table('catalogo_vendedor')
                ->where('id_vendedor', 4)
                ->where('id_catalogo_base', $item->id_catalogo)
                ->value('id_cv');

            if (!$idCv) {
                $idCv = DB::table('catalogo_vendedor')->insertGetId([
                    'id_vendedor'               => 4,
                    'id_catalogo_base'          => $item->id_catalogo,
                    'nombre_personalizado'      => $item->nombre,
                    'descripcion_personalizada' => $item->descripcion,
                    'tipo'                      => $item->tipo,
                    'id_categoria'              => $item->id_categoria,
                    'activo'                    => true,
                ], 'id_cv');

                $precioBase = DB::table('precios')->where('id_catalogo', $item->id_catalogo)->value('precio') ?? 5.00;

                DB::table('precios_vendedor')->insertOrIgnore([
                    'id_cv'        => $idCv,
                    'precio'       => $precioBase,
                    'fecha_inicio' => '2026-01-01',
                    'fecha_fin'    => null,
                ]);

                // Disponibilidad de vendedor (Lunes a Viernes de 8:00 a 18:00)
                $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                foreach ($dias as $dia) {
                    DB::table('disponibilidad_vendedor')->insertOrIgnore([
                        'id_cv'       => $idCv,
                        'dia_semana'  => $dia,
                        'hora_inicio' => '08:00:00',
                        'hora_fin'    => '18:00:00',
                        'disponible'  => true,
                    ]);
                }

                if ($item->id_categoria == $idTramites) {
                    DB::table('reglas_vendedor')->insertOrIgnore([
                        'id_cv'       => $idCv,
                        'descripcion' => 'Requiere presentar credencial universitaria física',
                        'tipo_regla'  => 'requisito_identificacion',
                    ]);
                }
            }
        }
        $this->command->info('Catálogo Vendedor 4 (Papelería y Trámites): OK');

        // ── 4. Vincular Tiendas Locales con Vendedores del Catálogo ──────────
        // Tienda 1: Cafetería Central -> vendedor_catalogo_id = 3
        Tienda::where('id', 1)->update(['vendedor_catalogo_id' => 3]);

        // Tienda 2: Papelería & Copias -> vendedor_catalogo_id = 4
        Tienda::where('id', 2)->update(['vendedor_catalogo_id' => 4]);

        $this->command->info('Vínculos de Tiendas Locales: OK');
        $this->command->info('VendedoresIntegracionSeeder finalizado con éxito.');
    }
}
