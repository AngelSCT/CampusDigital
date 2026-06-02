<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Carrito;
use App\Models\Cart\ItemCarrito;
use App\Models\Pedido;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\EloquentPedidoCreator;
use App\Modules\Cart\Services\ModuleTokenService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

/**
 * Prueba EloquentPedidoCreator contra la tabla `pedido` real.
 *
 * La tabla `pedido` original usa features PostgreSQL-específicas (triggers,
 * CHECK constraints vía PL/pgSQL). Esta clase la recrea en SQLite sin esas
 * restricciones, lo que permite probar el comportamiento de EloquentPedidoCreator
 * en el entorno de test in-memory. La semántica de datos es idéntica.
 */
class EloquentPedidoCreatorTest extends CartTestCase
{
    private \App\Models\Cart\ModuloCliente $modulo;
    private CarritoService $carritoService;
    private EloquentPedidoCreator $creator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();
        $this->crearTablaPedidoParaTests();

        $this->carritoService = new CarritoService();
        $this->creator        = new EloquentPedidoCreator();

        $this->modulo = $this->createTestModuloCliente([
            'slug'                   => 'biblioteca-eloquent-test',
            'tipo_modulo'            => 'biblioteca',
            'categorias_autorizadas' => ['prestamo'],
        ]);
    }

    // ─── TC: crearDesdeCarrito crea un Pedido ─────────────────────────────────

    #[Test]
    public function crear_desde_carrito_inserta_pedido_en_bd(): void
    {
        $carrito = $this->crearCarritoConItem();

        $this->creator->crearDesdeCarrito($carrito);

        $this->assertDatabaseHas('pedido', [
            'carrito_uuid' => (string) $carrito->uuid,
            'estado'       => 'creado',
            'modulo'       => 'biblioteca',
        ]);
    }

    #[Test]
    public function folio_derivado_es_determinista_para_el_mismo_carrito(): void
    {
        $carrito = $this->crearCarritoConItem();

        $this->creator->crearDesdeCarrito($carrito);

        $pedido = Pedido::where('carrito_uuid', (string) $carrito->uuid)->first();
        $this->assertStringStartsWith('PED-', $pedido->numero_folio);

        // El folio debe ser el mismo si lo calculamos de nuevo
        $hexUuid       = str_replace('-', '', (string) $carrito->uuid);
        $folioEsperado = 'PED-' . strtoupper(substr($hexUuid, 0, 20));
        $this->assertEquals($folioEsperado, $pedido->numero_folio);
    }

    // ─── TC: Idempotencia ─────────────────────────────────────────────────────

    #[Test]
    public function segunda_llamada_no_crea_pedido_duplicado(): void
    {
        $carrito = $this->crearCarritoConItem();

        $this->creator->crearDesdeCarrito($carrito);
        $this->creator->crearDesdeCarrito($carrito); // segunda llamada idempotente

        $this->assertEquals(1, Pedido::where('carrito_uuid', (string) $carrito->uuid)->count(),
            'crearDesdeCarrito debe ser idempotente: segunda llamada no crea otro Pedido');
    }

    // ─── TC: cancelarPedidoDeCarrito ─────────────────────────────────────────

    #[Test]
    public function cancelar_pedido_cambia_estado_a_cancelado(): void
    {
        $carrito = $this->crearCarritoConItem();
        $this->creator->crearDesdeCarrito($carrito);

        $this->creator->cancelarPedidoDeCarrito((string) $carrito->uuid);

        $this->assertDatabaseHas('pedido', [
            'carrito_uuid' => (string) $carrito->uuid,
            'estado'       => 'cancelado',
        ]);
    }

    #[Test]
    public function cancelar_pedido_inexistente_no_lanza(): void
    {
        // No debe lanzar excepción si el Pedido no existe
        $this->creator->cancelarPedidoDeCarrito('uuid-que-no-existe');
        $this->assertTrue(true); // llegamos aquí sin excepción
    }

    #[Test]
    public function cancelar_pedido_ya_cancelado_es_idempotente(): void
    {
        $carrito = $this->crearCarritoConItem();
        $this->creator->crearDesdeCarrito($carrito);
        $this->creator->cancelarPedidoDeCarrito((string) $carrito->uuid);
        $this->creator->cancelarPedidoDeCarrito((string) $carrito->uuid); // segunda vez

        $this->assertEquals(1, Pedido::where('carrito_uuid', (string) $carrito->uuid)->count());
        $this->assertEquals('cancelado', Pedido::where('carrito_uuid', (string) $carrito->uuid)->value('estado'));
    }

    // ─── TC: meta_json contiene los datos del carrito ─────────────────────────

    #[Test]
    public function meta_json_incluye_usuario_ref_y_items(): void
    {
        $carrito = $this->crearCarritoConItem();

        $this->creator->crearDesdeCarrito($carrito);

        $pedido = Pedido::where('carrito_uuid', (string) $carrito->uuid)->first();
        $meta   = $pedido->meta_json;

        $this->assertEquals((string) $carrito->uuid, $meta['carrito_uuid']);
        $this->assertEquals('42', $meta['usuario_ref']);
        $this->assertNotEmpty($meta['items']);
        $this->assertEquals('LIBRO-ETEST', $meta['items'][0]['referencia_externa']);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function crearCarritoConItem(): Carrito
    {
        // Usa usuario_ref numérico para simular producción (strval(auth()->id())).
        // EloquentPedidoCreator resuelve usuario_id = (int) usuario_ref sin consultar tabla usuario.
        $carrito = $this->carritoService->crear($this->modulo, [
            'usuario_ref'    => '42',
            'requiere_saldo' => false,
        ]);

        $cat = \App\Models\Cart\Categoria::where('slug', 'prestamo')->first();
        ItemCarrito::create([
            'carrito_id'         => $carrito->id,
            'categoria_id'       => $cat->id,
            'referencia_externa' => 'LIBRO-ETEST',
            'nombre'             => 'Libro EloquentTest',
            'precio_unitario'    => 75.00,
            'cantidad'           => 1,
            'added_at'           => now(),
        ]);
        $carrito->update(['total' => '75.00']);

        return $carrito->load('modulo');
    }

    /**
     * Crea la tabla `pedido` en SQLite sin features PostgreSQL-específicas.
     * Semánticamente equivalente a la migración real; omite triggers y CHECK constraints.
     */
    private function crearTablaPedidoParaTests(): void
    {
        Schema::create('pedido', function (Blueprint $table) {
            $table->id();
            $table->uuid('carrito_uuid')->nullable()->unique();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('numero_folio', 30)->unique();
            $table->string('estado', 30)->default('creado');
            $table->string('modulo', 50)->default('otro');
            $table->decimal('total', 10, 2)->default(0.00);
            $table->text('descripcion')->default('');
            $table->text('notas')->default('');
            $table->boolean('cobrado_de_saldo')->default(false);
            $table->json('meta_json')->default('{}');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
