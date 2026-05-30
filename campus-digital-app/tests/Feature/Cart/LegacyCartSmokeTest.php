<?php

namespace Tests\Feature\Cart;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Smoke test mínimo para el sistema legacy de carrito.
 *
 * Objetivo: confirmar que las rutas legadas siguen respondiendo correctamente
 * después de cualquier cambio. No verifica lógica financiera.
 *
 * REQUISITO: PostgreSQL.
 * El monorepo tiene migraciones PostgreSQL-específicas (citext, PL/pgSQL) que
 * no corren en SQLite. Sin RefreshDatabase: el skip en setUp() ocurre antes de
 * cualquier intento de migración.
 * En CI con PostgreSQL se usa: DB_CONNECTION=pgsql php artisan test --filter LegacyCartSmokeTest
 *
 * POST /api/carrito NO se incluye: requiere factories de Producto/CarritoItem
 * que no forman parte del sistema nuevo. Agregar en Fase 2 cuando se migre.
 */
class LegacyCartSmokeTest extends TestCase
{
    // NO RefreshDatabase: las migraciones del monorepo no corren en SQLite.
    // En PostgreSQL, ejecutar migrate:fresh manualmente o con DB_CONNECTION=pgsql.

    protected function setUp(): void
    {
        parent::setUp(); // Bootea la app; sin RefreshDatabase no hay intento de migración

        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped(
                'LegacyCartSmokeTest requiere PostgreSQL. ' .
                'Las migraciones del monorepo (citext, PL/pgSQL) no corren en SQLite. ' .
                'Ejecutar con: DB_CONNECTION=pgsql php artisan test --filter LegacyCartSmokeTest'
            );
        }
    }

    #[Test]
    public function get_carrito_con_usuario_autenticado_devuelve_200(): void
    {
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario)
            ->getJson('/api/carrito')
            ->assertOk()
            ->assertJsonStructure(['carrito', 'total', 'monedero']);
    }
}
