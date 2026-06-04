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
 * Usa DatabaseTransactions sobre la base de datos de tests en PostgreSQL.
 */
abstract class CartTestCase extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;
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
        parent::tearDown();
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


}
