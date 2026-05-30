<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration idempotente: inserta reglas de precio en las categorías base del módulo Carrito.
 *
 * ┌─────────────────────────────────────────────────────────────────────────────┐
 * │  FLUJO DE DESPLIEGUE — dos escenarios                                       │
 * ├─────────────────────────────────────────────────────────────────────────────┤
 * │  BD EXISTENTE (cart_categorias ya tiene registros):                         │
 * │    php artisan migrate                                                      │
 * │    → esta migración parchea/inserta las reglas de precio.                   │
 * │    → si faltan categorías base requeridas → RuntimeException (fallo claro). │
 * │                                                                             │
 * │  BD FRESCA (cart_categorias está vacía):                                    │
 * │    php artisan migrate                   → esta migración retorna sin error  │
 * │    php artisan db:seed --class=CategoriasSeeder  ← OBLIGATORIO             │
 * │                           (o: php artisan migrate --seed)                   │
 * │    → el seeder crea categorías + reglas de precio juntas.                   │
 * │                                                                             │
 * │  En BD fresca, producción depende de que el seeder se corra.                │
 * │  Esta migración NO crea categorías por sí sola.                             │
 * └─────────────────────────────────────────────────────────────────────────────┘
 *
 * Categorías REQUERIDAS (RuntimeException si existen filas en cart_categorias
 * pero falta alguna de estas):
 *   prestamo, reserva, producto, servicio, ticket
 *   → Son las 5 categorías base del CategoriasSeeder.
 *
 * Categorías OPCIONALES (se omiten sin error si no existen):
 *   copias, impresiones
 *   → No forman parte del seeder base; se insertan si el módulo las registra
 *     por separado en una migración de datos futura.
 */
return new class extends Migration
{
    private const REQUERIDAS = ['prestamo', 'reserva', 'producto', 'servicio', 'ticket'];
    private const OPCIONALES  = ['copias', 'impresiones'];

    public function up(): void
    {
        // ── Verificar estado de cart_categorias ───────────────────────────────
        // Si la tabla está completamente vacía es un deploy fresco: el seeder aún no corrió.
        // En ese caso, el seeder creará las categorías CON reglas de precio → omitir.
        // Si la tabla tiene filas pero faltan las requeridas → error de estado incompleto.
        $totalCategorias = DB::table('cart_categorias')->count();

        if ($totalCategorias === 0) {
            // BD fresca: cart_categorias todavía está vacía.
            // Las reglas de precio se crearán cuando corra CategoriasSeeder,
            // que ya incluye CLAVE_PERMITE_PRECIO_CERO y CLAVE_PRECIO_MINIMO.
            // REQUERIDO en despliegue fresco: php artisan db:seed --class=CategoriasSeeder
            return;
        }

        $faltantes = [];
        foreach (self::REQUERIDAS as $slug) {
            if (!DB::table('cart_categorias')->where('slug', $slug)->exists()) {
                $faltantes[] = $slug;
            }
        }

        if (!empty($faltantes)) {
            throw new \RuntimeException(
                "Data migration fallida: cart_categorias tiene registros pero faltan categorías base requeridas: "
                . implode(', ', $faltantes)
                . ". Correr primero: php artisan db:seed --class=CategoriasSeeder"
            );
        }

        // ── Definición de reglas ───────────────────────────────────────────────
        $reglas = [
            // REQUERIDAS
            'prestamo' => [
                ['permite_precio_cero', 'true',  'bool'],
            ],
            'reserva' => [
                ['permite_precio_cero', 'true',  'bool'],
            ],
            'producto' => [
                ['permite_precio_cero', 'false', 'bool'],
                ['precio_minimo',       '0.01',  'string'],
            ],
            'servicio' => [
                ['permite_precio_cero', 'false', 'bool'],
                ['precio_minimo',       '0.01',  'string'],
            ],
            'ticket' => [
                ['permite_precio_cero', 'false', 'bool'],
                ['precio_minimo',       '0.01',  'string'],
            ],
            // OPCIONALES — omitir silenciosamente si no existen
            'copias' => [
                ['permite_precio_cero', 'false', 'bool'],
                ['precio_minimo',       '0.01',  'string'],
            ],
            'impresiones' => [
                ['permite_precio_cero', 'false', 'bool'],
                ['precio_minimo',       '0.01',  'string'],
            ],
        ];

        $now = now();

        foreach ($reglas as $slug => $slugReglas) {
            $categoria = DB::table('cart_categorias')->where('slug', $slug)->first();

            if (!$categoria) {
                // Solo las opcionales pueden llegar aquí (las requeridas ya se validaron arriba)
                continue;
            }

            foreach ($slugReglas as [$clave, $valor, $tipoDato]) {
                DB::table('cart_reglas_categoria')->updateOrInsert(
                    ['categoria_id' => $categoria->id, 'clave' => $clave],
                    ['valor' => $valor, 'tipo_dato' => $tipoDato, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        // No revertir: eliminar reglas de precio restaura el comportamiento inseguro
        // (precio 0 permitido en todas las categorías).
        // Si se necesita revertir, hacerlo manualmente con criterio de negocio.
    }
};
