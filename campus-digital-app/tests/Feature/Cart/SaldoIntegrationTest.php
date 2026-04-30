<?php

namespace Tests\Feature\Cart;

use App\Jobs\ReintentaConciliacion;
use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\Categoria;
use App\Models\Cart\ConciliacionPendiente;
use App\Models\Cart\ItemCarrito;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\ModuleTokenService;
use App\Modules\Cart\Services\SaldoClient;
use App\Modules\Cart\Services\SaldoConfirmed;
use App\Modules\Cart\Services\SaldoInsufficientFunds;
use App\Modules\Cart\Services\SaldoResult;
use App\Modules\Cart\Services\SaldoUnavailable;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;

class SaldoIntegrationTest extends CartTestCase
{
    private \App\Models\Cart\ModuloCliente $modulo;
    private string $token;
    private CarritoService $carritoService;
    private ModuleTokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();

        config([
            'cart.saldo.base_url'                   => 'http://saldo.test',
            'cart.saldo.tope_pendiente_por_usuario' => 200.0,
            'cart.saldo.tope_pendiente_global'      => 50000.0,
            'cart.saldo.reintentos_max'              => 5,
        ]);

        $this->carritoService = new CarritoService();
        $this->tokenService   = new ModuleTokenService();

        $this->modulo = $this->createTestModuloCliente([
            'slug'                   => 'biblioteca-saldo',
            'tipo_modulo'            => 'biblioteca',
            'categorias_autorizadas' => ['prestamo', 'reserva'],
        ]);

        $par         = $this->tokenService->issuePair($this->modulo);
        $this->token = $par['access_token'];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Inyecta un SaldoClient stub en el contenedor.
     * Todos los métodos deben estar implementados para evitar llamadas a la red.
     */
    private function bindSaldoClientStub(
        SaldoResult $resultReservar,
        bool        $confirmar     = true,
        bool        $liberar       = true,
        bool        $cargoForzoso  = true,
    ): void {
        $stub = new class($resultReservar, $confirmar, $liberar, $cargoForzoso) extends SaldoClient {
            public bool $liberarLlamado   = false;
            public bool $confirmarLlamado = false;
            public function __construct(
                private readonly SaldoResult $r,
                private readonly bool $conf,
                private readonly bool $lib,
                private readonly bool $cargo,
            ) {}
            public function reservar(string $u, float $m, string $c, string $slug, string $concepto): SaldoResult
            {
                return $this->r;
            }
            public function confirmar(string $reservaId, string $carritoUuid): bool
            {
                $this->confirmarLlamado = true;
                return $this->conf;
            }
            public function liberar(string $reservaId): bool
            {
                $this->liberarLlamado = true;
                return $this->lib;
            }
            public function cargoForzoso(string $u, float $m, string $c, string $concepto, ?string $estado = null): bool
            {
                return $this->cargo;
            }
        };
        $this->app->instance(SaldoClient::class, $stub);
    }

    /** Crea un SaldoClient stub anónimo para uso directo en tests de job. */
    private function makeSaldoClientStub(bool $cargoForzoso = true): SaldoClient
    {
        return new class($cargoForzoso) extends SaldoClient {
            public function __construct(private readonly bool $cargo) {}
            public function reservar(string $u, float $m, string $c, string $slug, string $concepto): SaldoResult
            {
                return new SaldoUnavailable();
            }
            public function confirmar(string $reservaId, string $carritoUuid): bool { return true; }
            public function liberar(string $reservaId): bool                        { return true; }
            public function cargoForzoso(string $u, float $m, string $c, string $concepto, ?string $estado = null): bool
            {
                return $this->cargo;
            }
        };
    }

    private function crearCarritoConSaldo(string $usuarioRef = 'MAT-SALDO', bool $requiereSaldo = true): Carrito
    {
        return $this->carritoService->crear($this->modulo, [
            'usuario_ref'    => $usuarioRef,
            'requiere_saldo' => $requiereSaldo,
        ]);
    }

    private function agregarItem(Carrito $carrito, string $categoriaSlug, float $precio = 50.00): ItemCarrito
    {
        $cat = Categoria::where('slug', $categoriaSlug)->first();
        return ItemCarrito::create([
            'carrito_id'         => $carrito->id,
            'categoria_id'       => $cat->id,
            'referencia_externa' => 'REF-' . $categoriaSlug,
            'nombre'             => "Ítem {$categoriaSlug}",
            'precio_unitario'    => $precio,
            'cantidad'           => 1,
            'added_at'           => now(),
        ]);
    }

    private function crearConciliacion(int $intentos = 0, string $usuarioRef = 'MAT-JOB'): ConciliacionPendiente
    {
        $carrito = Carrito::create([
            'uuid'           => 'uuid-job-' . uniqid(),
            'modulo_id'      => $this->modulo->id,
            'usuario_ref'    => $usuarioRef,
            'estado'         => Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION,
            'requiere_saldo' => true,
            'total'          => '50.00',
        ]);

        return ConciliacionPendiente::create([
            'carrito_uuid'        => $carrito->uuid,
            'modulo_id'           => $this->modulo->id,
            'usuario_ref'         => $usuarioRef,
            'monto'               => 50.00,
            'intentos'            => $intentos,
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_PENDIENTE,
        ]);
    }

    // ─── Tests de Checkout con Saldo ─────────────────────────────────────────

    #[Test]
    public function checkout_con_saldo_confirmado_pasa_a_estado_confirmado(): void
    {
        $this->bindSaldoClientStub(new SaldoConfirmed('RSRV-001', '2026-05-01T00:00:00Z'));

        $carrito = $this->crearCarritoConSaldo();
        $this->agregarItem($carrito, 'prestamo', 50.00);
        $carrito->update(['total' => '50.00']);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertOk()
            ->assertJsonPath('estado', Carrito::ESTADO_CONFIRMADO);
    }

    #[Test]
    public function checkout_con_fondos_insuficientes_devuelve_402(): void
    {
        $this->bindSaldoClientStub(new SaldoInsufficientFunds());

        $carrito = $this->crearCarritoConSaldo();
        $this->agregarItem($carrito, 'prestamo', 50.00);
        $carrito->update(['total' => '50.00']);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(402)
            ->assertJsonPath('error', 'SALDO_INSUFICIENTE');

        $this->assertEquals(Carrito::ESTADO_ABIERTO, $carrito->fresh()->estado);
    }

    #[Test]
    public function checkout_con_saldo_caido_y_categoria_sin_diferido_devuelve_503(): void
    {
        $this->bindSaldoClientStub(new SaldoUnavailable());

        $carrito = $this->crearCarritoConSaldo();
        $this->agregarItem($carrito, 'reserva', 100.00);
        $carrito->update(['total' => '100.00']);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(503)
            ->assertJsonPath('error', 'SALDO_NO_DISPONIBLE');
    }

    #[Test]
    public function checkout_con_saldo_caido_y_permite_diferido_dentro_de_topes(): void
    {
        $this->bindSaldoClientStub(new SaldoUnavailable());

        $carrito = $this->crearCarritoConSaldo();
        $this->agregarItem($carrito, 'prestamo', 50.00);
        $carrito->update(['total' => '50.00']);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertOk()
            ->assertJsonPath('estado', Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION)
            ->assertJsonStructure(['aviso']);

        $this->assertDatabaseHas('cart_conciliaciones_pendientes', [
            'carrito_uuid'        => $carrito->uuid,
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_PENDIENTE,
        ]);
    }

    #[Test]
    public function checkout_con_tope_por_usuario_rebasado_devuelve_503(): void
    {
        $this->bindSaldoClientStub(new SaldoUnavailable());
        config(['cart.saldo.tope_pendiente_por_usuario' => 50.0]);

        ConciliacionPendiente::create([
            'carrito_uuid'        => 'uuid-anterior',
            'modulo_id'           => $this->modulo->id,
            'usuario_ref'         => 'MAT-SALDO',
            'monto'               => 45.00,
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_PENDIENTE,
        ]);

        $carrito = $this->crearCarritoConSaldo('MAT-SALDO');
        $this->agregarItem($carrito, 'prestamo', 30.00);
        $carrito->update(['total' => '30.00']);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(503)
            ->assertJsonPath('error', 'SALDO_NO_DISPONIBLE');
    }

    #[Test]
    public function checkout_con_tope_global_rebasado_devuelve_503(): void
    {
        $this->bindSaldoClientStub(new SaldoUnavailable());
        config(['cart.saldo.tope_pendiente_global' => 100.0]);

        ConciliacionPendiente::create([
            'carrito_uuid'        => 'uuid-global',
            'modulo_id'           => $this->modulo->id,
            'usuario_ref'         => 'OTRO-USUARIO',
            'monto'               => 90.00,
            'estado_conciliacion' => ConciliacionPendiente::ESTADO_PENDIENTE,
        ]);

        $carrito = $this->crearCarritoConSaldo('MAT-NUEVO');
        $this->agregarItem($carrito, 'prestamo', 30.00);
        $carrito->update(['total' => '30.00']);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(503)
            ->assertJsonPath('error', 'SALDO_NO_DISPONIBLE');
    }

    // ─── Nuevos tests de ciclo reservar/confirmar/liberar ────────────────────

    #[Test]
    public function reservar_exitoso_pero_confirmar_falla_devuelve_carrito_abierto_y_registra_incidente(): void
    {
        // confirmar() devuelve false (409 simulado)
        $this->bindSaldoClientStub(
            resultReservar: new SaldoConfirmed('RSRV-FAIL', '2026-05-01T00:00:00Z'),
            confirmar:      false,
        );

        $carrito = $this->crearCarritoConSaldo();
        $this->agregarItem($carrito, 'prestamo', 50.00);
        $carrito->update(['total' => '50.00']);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertOk();

        // El carrito debe haber vuelto a abierto
        $this->assertEquals(Carrito::ESTADO_ABIERTO, $carrito->fresh()->estado);

        // Incidente registrado en bitácora
        $this->assertDatabaseHas('cart_bitacora', [
            'accion'       => 'saldo.confirmar_fallido',
            'carrito_uuid' => $carrito->uuid,
        ]);
    }

    #[Test]
    public function excepcion_entre_reservar_y_confirmar_llama_a_liberar_para_rollback(): void
    {
        // Stub que lanza excepción en confirmarDirecto simulando fallo antes de confirmar()
        $liberarLlamado = false;
        $stub = new class($liberarLlamado) extends SaldoClient {
            public bool $liberarLlamado = false;
            public function reservar(string $u, float $m, string $c, string $slug, string $concepto): SaldoResult
            {
                return new SaldoConfirmed('RSRV-EXC');
            }
            public function confirmar(string $reservaId, string $carritoUuid): bool
            {
                // Simula que confirmar() mismo lanza excepción inesperada
                throw new \RuntimeException('Error inesperado durante confirmar');
            }
            public function liberar(string $reservaId): bool
            {
                $this->liberarLlamado = true;
                return true;
            }
            public function cargoForzoso(string $u, float $m, string $c, string $concepto, ?string $estado = null): bool
            {
                return true;
            }
        };
        $this->app->instance(SaldoClient::class, $stub);

        $carrito = $this->crearCarritoConSaldo();
        $this->agregarItem($carrito, 'prestamo', 50.00);
        $carrito->update(['total' => '50.00']);

        // La excepción debe propagarse (500)
        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(500);

        // liberar() debe haber sido llamado para hacer rollback de la reserva
        $this->assertTrue($stub->liberarLlamado, 'liberar() debe ser llamado al ocurrir una excepción inesperada');
    }

    // ─── Tests del Job ReintentaConciliacion ─────────────────────────────────

    #[Test]
    public function job_con_cargo_forzoso_exitoso_pasa_carrito_a_confirmado(): void
    {
        $conciliacion = $this->crearConciliacion();
        $job          = new ReintentaConciliacion($conciliacion);
        $job->handle($this->makeSaldoClientStub(cargoForzoso: true));

        $conciliacion->refresh();
        $this->assertEquals(ConciliacionPendiente::ESTADO_EXITOSA, $conciliacion->estado_conciliacion);

        $carrito = Carrito::where('uuid', $conciliacion->carrito_uuid)->first();
        $this->assertEquals(Carrito::ESTADO_CONFIRMADO, $carrito->estado);

        $this->assertDatabaseHas('cart_bitacora', ['accion' => 'conciliacion.exitosa']);
    }

    #[Test]
    public function job_con_cargo_forzoso_fallido_incrementa_intentos_y_reagenda(): void
    {
        Queue::fake();

        $conciliacion = $this->crearConciliacion(intentos: 0);
        $job          = new ReintentaConciliacion($conciliacion);
        $job->handle($this->makeSaldoClientStub(cargoForzoso: false));

        $conciliacion->refresh();
        $this->assertEquals(1, $conciliacion->intentos);
        $this->assertNotNull($conciliacion->proximo_intento_at);
        $this->assertEquals(ConciliacionPendiente::ESTADO_PENDIENTE, $conciliacion->estado_conciliacion);

        Queue::assertPushed(ReintentaConciliacion::class);
    }

    #[Test]
    public function job_despues_de_n_intentos_marca_requiere_revision_manual(): void
    {
        config(['cart.saldo.reintentos_max' => 5]);

        $conciliacion = $this->crearConciliacion(intentos: 4);
        $job          = new ReintentaConciliacion($conciliacion);
        $job->handle($this->makeSaldoClientStub(cargoForzoso: false));

        $conciliacion->refresh();
        $this->assertEquals(5, $conciliacion->intentos);
        $this->assertEquals(ConciliacionPendiente::ESTADO_REQUIERE_REVISION, $conciliacion->estado_conciliacion);
        $this->assertDatabaseHas('cart_bitacora', ['accion' => 'conciliacion.revision_manual']);
    }
}
