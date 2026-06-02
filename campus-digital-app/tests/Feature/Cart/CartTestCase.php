<?php

namespace Tests\Feature\Cart;

use App\Modules\Cart\Contracts\PedidoCreatorInterface;
use App\Modules\Cart\Services\NullPedidoCreator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Clase base para todos los tests del módulo Carrito.
 *
 * Corre SOLO las migraciones del módulo (2026_04_28_*) sobre SQLite en memoria,
 * evitando las migraciones PostgreSQL-específicas del monorepo (citext, funciones PL/pgSQL).
 * No usa RefreshDatabase para no depender del comando artisan migrate global.
 */
abstract class CartTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSqliteInMemory();
        $this->runCartMigrations();

        // Tests usan NullPedidoCreator: la tabla 'pedido' no existe en SQLite in-memory.
        // AppServiceProvider liga EloquentPedidoCreator en producción; aquí usamos el stub.
        $this->app->bind(PedidoCreatorInterface::class, NullPedidoCreator::class);
    }

    protected function tearDown(): void
    {
        // La BD in-memory se destruye automáticamente al cerrar la conexión.
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    private function setUpSqliteInMemory(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver'                  => 'sqlite',
                'database'                => ':memory:',
                'prefix'                  => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
    }

    /** Configura el JWT del módulo Carrito con valores de prueba fijos. */
    protected function setUpJwtConfig(): void
    {
        config([
            // 64 chars hex = 256 bits — secreto fijo solo para tests
            'cart.jwt.secret'      => str_repeat('ab', 32),
            'cart.jwt.ttl_access'  => 3600,
            'cart.jwt.ttl_refresh' => 604800,
        ]);
    }

    /** Crea un ModuloCliente mínimo para tests (sin FK real gracias a foreign_key_constraints=false). */
    protected function createTestModuloCliente(array $override = []): \App\Models\Cart\ModuloCliente
    {
        return \App\Models\Cart\ModuloCliente::create(array_merge([
            'solicitud_id'           => 1,
            'nombre'                 => 'Módulo Test',
            'slug'                   => 'test-modulo-' . uniqid(),
            'tipo_modulo'            => 'test',
            'categorias_autorizadas' => ['prestamo', 'reserva'],
            'activo'                 => true,
        ], $override));
    }

    /** Inserta las 5 categorías base en la BD de prueba. */
    protected function seedCategorias(): void
    {
        (new \Database\Seeders\CategoriasSeeder())->run();
    }

    private function runCartMigrations(): void
    {
        $files = glob(database_path('migrations/2026_04_28_*.php'));
        sort($files);

        foreach ($files as $file) {
            // require (no require_once) para que cada setUp reciba una instancia fresca.
            (require $file)->up();
        }
    }
}
