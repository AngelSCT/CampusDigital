<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\Categoria;
use App\Models\Cart\ItemCarrito;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\ModuleTokenService;
use App\Modules\Cart\Services\SaldoClient;
use App\Modules\Cart\Services\SaldoConfirmed;
use App\Modules\Cart\Services\SaldoResult;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests de integración Checkout → Pedidos (Capa 8).
 *
 * La tabla `pedido` usa features PostgreSQL-específicas (triggers, PL/pgSQL)
 * que no corren en SQLite. Por eso los tests verifican el comportamiento de
 * CheckoutService contra la PedidoCreatorInterface mediante spies en memoria,
 * sin tocar la tabla `pedido` real. Este es el patrón correcto: testear que
 * el servicio llama al contrato, no la implementación concreta.
 */
class PedidoIntegrationTest extends CartTestCase
{
    private \App\Models\Cart\ModuloCliente $modulo;
    private string $token;
    private CarritoService $carritoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();

        config([
            'cart.saldo.base_url'                   => 'http://saldo.test',
            'cart.saldo.tope_pendiente_por_usuario'  => 200.0,
            'cart.saldo.tope_pendiente_global'        => 50000.0,
        ]);

        $this->carritoService = new CarritoService();

        $this->modulo = $this->createTestModuloCliente([
            'slug'                   => 'biblioteca-pedido-test',
            'tipo_modulo'            => 'biblioteca',
            'categorias_autorizadas' => ['prestamo', 'reserva'],
        ]);

        $par         = (new ModuleTokenService())->issuePair($this->modulo);
        $this->token = $par['access_token'];
    }

    // ─── TC: Confirmación sin Saldo crea Pedido ───────────────────────────────

    #[Test]
    public function confirmacion_directa_llama_crear_pedido_una_vez(): void
    {
        $spy = $this->makePedidoSpy();
        $this->app->instance(PedidoCreatorInterface::class, $spy);

        $carrito = $this->crearCarritoConItem(requiereSaldo: false);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertOk()
            ->assertJsonPath('estado', Carrito::ESTADO_CONFIRMADO);

        $this->assertEquals(1, $spy->crearCalls,   'crearDesdeCarrito debe llamarse exactamente una vez');
        $this->assertEquals(0, $spy->cancelarCalls, 'cancelarPedidoDeCarrito no debe llamarse en el happy path');

        $this->assertDatabaseHas('cart_bitacora', [
            'accion'       => Bitacora::ACCION_PEDIDO_CREADO,
            'carrito_uuid' => $carrito->uuid,
        ]);
    }

    // ─── TC: Fallo de crearDesdeCarrito revierte el Carrito ──────────────────

    #[Test]
    public function fallo_al_crear_pedido_deja_carrito_en_revertido(): void
    {
        $spy = $this->makePedidoSpy(shouldThrow: true);
        $this->app->instance(PedidoCreatorInterface::class, $spy);

        $carrito = $this->crearCarritoConItem(requiereSaldo: false);

        // La excepción de PedidoCreator llega al CheckoutController como 500,
        // pero lo importante es que el Carrito quede en 'revertido'.
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout");

        $this->assertEquals(
            Carrito::ESTADO_REVERTIDO,
            $carrito->fresh()->estado,
            'El Carrito debe quedar en revertido cuando crearDesdeCarrito falla'
        );

        $this->assertDatabaseHas('cart_bitacora', [
            'accion'       => Bitacora::ACCION_CARRITO_REVERTIDO,
            'carrito_uuid' => $carrito->uuid,
        ]);
    }

    // ─── TC: Confirmación con Saldo — orden: reservar → BD → confirmar ───────

    #[Test]
    public function checkout_con_saldo_llama_crear_pedido_antes_de_confirmar_saldo(): void
    {
        $callOrder = [];

        $spy = $this->makePedidoSpyWithLog($callOrder, 'crear');
        $this->app->instance(PedidoCreatorInterface::class, $spy);

        $saldoStub = $this->bindSaldoStubWithLog($callOrder, new SaldoConfirmed('RSRV-X', ''));
        $this->app->instance(SaldoClient::class, $saldoStub);

        $carrito = $this->crearCarritoConItem(requiereSaldo: true);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertOk();

        $this->assertEquals(['reservar', 'crear', 'confirmar'], $callOrder,
            'Orden obligatorio: reservar(HTTP) → crearDesdeCarrito(BD) → confirmar(HTTP)');
    }

    // ─── TC: confirmar() false post-commit → compensar ───────────────────────

    #[Test]
    public function confirmar_saldo_false_post_commit_cancela_pedido_y_revierte_carrito(): void
    {
        $spy = $this->makePedidoSpy();
        $this->app->instance(PedidoCreatorInterface::class, $spy);

        // SaldoClient donde confirmar() devuelve false (409)
        $saldoStub = new class(new SaldoConfirmed('RSRV-409', '')) extends SaldoClient {
            public bool $liberarLlamado = false;
            public function __construct(private SaldoResult $r) {}
            public function reservar(string $u, float $m, string $c, string $s, string $co): SaldoResult { return $this->r; }
            public function confirmar(string $reservaId, string $carritoUuid): bool { return false; } // 409
            public function liberar(string $reservaId): bool { $this->liberarLlamado = true; return true; }
            public function cargoForzoso(string $u, float $m, string $c, string $co, ?string $e = null): \App\Modules\Cart\Services\SaldoResult { return new \App\Modules\Cart\Services\CargoForzosoCobrado(); }
        };
        $this->app->instance(SaldoClient::class, $saldoStub);

        $carrito = $this->crearCarritoConItem(requiereSaldo: true);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(409)
            ->assertJsonPath('error', 'CHECKOUT_REVERTIDO');

        $this->assertEquals(1, $spy->cancelarCalls,
            'cancelarPedidoDeCarrito debe llamarse ante confirmar_409');
        $this->assertTrue($saldoStub->liberarLlamado,
            'liberar() debe llamarse para devolver los fondos');
        $this->assertEquals(
            Carrito::ESTADO_REVERTIDO,
            $carrito->fresh()->estado,
            'El Carrito debe quedar en revertido'
        );

        $this->assertDatabaseHas('cart_bitacora', [
            'accion'       => Bitacora::ACCION_PEDIDO_CANCELADO,
            'carrito_uuid' => $carrito->uuid,
        ]);
        $this->assertDatabaseHas('cart_bitacora', [
            'accion'       => Bitacora::ACCION_CARRITO_REVERTIDO,
            'carrito_uuid' => $carrito->uuid,
        ]);
    }

    // ─── TC: Idempotencia — segundo checkout rechazado por assertOperable ─────

    #[Test]
    public function segundo_checkout_del_mismo_carrito_es_rechazado(): void
    {
        $spy = $this->makePedidoSpy();
        $this->app->instance(PedidoCreatorInterface::class, $spy);

        $carrito = $this->crearCarritoConItem(requiereSaldo: false);

        // Primera llamada — éxito
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertOk();

        // Segunda llamada — debe rechazarse (carrito ya está en estado terminal)
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(409); // CartStateException → CART_STATE_ERROR

        $this->assertEquals(1, $spy->crearCalls,
            'crearDesdeCarrito debe llamarse solo una vez aunque el endpoint se llame dos veces');
    }

    // ─── TC: Fallo de Pedido con Saldo libera la reserva ─────────────────────

    #[Test]
    public function fallo_crear_pedido_con_saldo_libera_la_reserva(): void
    {
        $spy = $this->makePedidoSpy(shouldThrow: true);
        $this->app->instance(PedidoCreatorInterface::class, $spy);

        $liberarLlamado = false;
        $saldoStub = new class(new SaldoConfirmed('RSRV-LIB', ''), $liberarLlamado) extends SaldoClient {
            public bool $liberarLlamado = false;
            public function __construct(private SaldoResult $r, bool $_) {}
            public function reservar(string $u, float $m, string $c, string $s, string $co): SaldoResult { return $this->r; }
            public function confirmar(string $reservaId, string $carritoUuid): bool { return true; }
            public function liberar(string $reservaId): bool { $this->liberarLlamado = true; return true; }
            public function cargoForzoso(string $u, float $m, string $c, string $co, ?string $e = null): \App\Modules\Cart\Services\SaldoResult { return new \App\Modules\Cart\Services\CargoForzosoCobrado(); }
        };
        $this->app->instance(SaldoClient::class, $saldoStub);

        $carrito = $this->crearCarritoConItem(requiereSaldo: true);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout");

        $this->assertTrue($saldoStub->liberarLlamado,
            'liberar() debe llamarse cuando crearDesdeCarrito falla (pre-commit)');
        $this->assertEquals(
            Carrito::ESTADO_REVERTIDO,
            $carrito->fresh()->estado
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function crearCarritoConItem(bool $requiereSaldo): Carrito
    {
        $carrito = $this->carritoService->crear($this->modulo, [
            'usuario_ref'    => 'MAT-PEDIDO-TEST',
            'requiere_saldo' => $requiereSaldo,
        ]);

        $cat = Categoria::where('slug', 'prestamo')->first();
        ItemCarrito::create([
            'carrito_id'         => $carrito->id,
            'categoria_id'       => $cat->id,
            'referencia_externa' => 'LIBRO-TEST',
            'nombre'             => 'Libro de Prueba',
            'precio_unitario'    => 50.00,
            'cantidad'           => 1,
            'added_at'           => now(),
        ]);
        $carrito->update(['total' => '50.00']);

        return $carrito;
    }

    /** Spy simple: registra llamadas y puede lanzar excepción en crearDesdeCarrito. */
    private function makePedidoSpy(bool $shouldThrow = false): object
    {
        return new class($shouldThrow) implements PedidoCreatorInterface {
            public int $crearCalls    = 0;
            public int $cancelarCalls = 0;

            public function __construct(private readonly bool $throw) {}

            public function crearDesdeCarrito(\App\Models\Cart\Carrito $carrito): void
            {
                $this->crearCalls++;
                if ($this->throw) {
                    throw new \RuntimeException('PedidoCreator falló intencionalmente en test');
                }
            }

            public function cancelarPedidoDeCarrito(string $carritoUuid): void
            {
                $this->cancelarCalls++;
            }
        };
    }

    /** Spy que registra el orden de llamadas en un array compartido. */
    private function makePedidoSpyWithLog(array &$log, string $tag): object
    {
        return new class($log, $tag) implements PedidoCreatorInterface {
            public function __construct(private array &$log, private string $tag) {}

            public function crearDesdeCarrito(\App\Models\Cart\Carrito $carrito): void
            {
                $this->log[] = $this->tag;
            }

            public function cancelarPedidoDeCarrito(string $carritoUuid): void {}
        };
    }

    /** SaldoClient stub que registra llamadas en el array de orden compartido. */
    private function bindSaldoStubWithLog(array &$callOrder, SaldoResult $resultReservar): SaldoClient
    {
        return new class($callOrder, $resultReservar) extends SaldoClient {
            public function __construct(private array &$log, private SaldoResult $r) {}

            public function reservar(string $u, float $m, string $c, string $s, string $co): SaldoResult
            {
                $this->log[] = 'reservar';
                return $this->r;
            }

            public function confirmar(string $reservaId, string $carritoUuid): bool
            {
                $this->log[] = 'confirmar';
                return true;
            }

            public function liberar(string $reservaId): bool { return true; }

            public function cargoForzoso(string $u, float $m, string $c, string $co, ?string $e = null): \App\Modules\Cart\Services\SaldoResult { return new \App\Modules\Cart\Services\CargoForzosoCobrado(); }
        };
    }
}
