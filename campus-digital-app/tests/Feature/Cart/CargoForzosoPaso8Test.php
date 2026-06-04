<?php

namespace Tests\Feature\Cart;

use App\Console\Commands\Cart\ReconciliarSaldoCommand;
use App\Models\Cart\Carrito;
use App\Models\Cart\ConciliacionPendiente;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;
use App\Modules\Cart\Services\CargoForzosoCobrado;
use App\Modules\Cart\Services\CargoForzosoDesconocido;
use App\Modules\Cart\Services\CargoForzosoRechazado;
use App\Modules\Cart\Services\NullPedidoCreator;
use App\Modules\Cart\Services\SaldoClient;
use App\Modules\Cart\Services\SaldoResult;
use App\Modules\Cart\Services\SaldoUnavailable;
use App\Jobs\ReintentaConciliacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests para Paso 7 y Paso 8 de Fase 0.
 * Cubre:
 *  - SaldoClient::cargoForzoso() → tipos de retorno correctos por HTTP status
 *  - ReintentaConciliacion con dos TX + tres ramas (Cobrado/Rechazado/Desconocido)
 *  - ReconciliarSaldoCommand: limpieza TTL de procesando (conciliaciones y carritos)
 */
class CargoForzosoPaso8Test extends CartTestCase
{
    private \App\Models\Cart\ModuloCliente $modulo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();

        config([
            'cart.saldo.base_url'                   => 'http://saldo.test',
            'cart.saldo.timeout'                     => 3,
            'cart.saldo.internal_token'              => 'test-token',
            'cart.saldo.procesando_ttl_minutos'      => 10,
            'cart.saldo.reintentos_max'              => 5,
            'cart.checkout.procesando_ttl_minutos'   => 10,
        ]);

        $this->modulo = $this->createTestModuloCliente([
            'slug'                   => 'test-cargo-' . uniqid(),
            'tipo_modulo'            => 'test',
            'categorias_autorizadas' => ['prestamo'],
        ]);
    }

    // ─── SaldoClient::cargoForzoso() — retornos por código HTTP ─────────────

    #[Test]
    public function cargo_forzoso_http_2xx_devuelve_cargo_cobrado(): void
    {
        Http::fake(['http://saldo.test/*' => Http::response([], 200)]);

        $client = new SaldoClient();
        $result = $client->cargoForzoso('USR-01', 50.0, 'uuid-001', 'test');

        $this->assertInstanceOf(CargoForzosoCobrado::class, $result);
    }

    #[Test]
    public function cargo_forzoso_http_400_devuelve_rechazado(): void
    {
        Http::fake(['http://saldo.test/*' => Http::response(['error' => 'bad_request'], 400)]);

        $client = new SaldoClient();
        $result = $client->cargoForzoso('USR-01', 50.0, 'uuid-001', 'test');

        $this->assertInstanceOf(CargoForzosoRechazado::class, $result);
    }

    #[Test]
    public function cargo_forzoso_http_402_devuelve_rechazado(): void
    {
        Http::fake(['http://saldo.test/*' => Http::response(['error' => 'fondos_insuficientes'], 402)]);

        $client = new SaldoClient();
        $result = $client->cargoForzoso('USR-01', 50.0, 'uuid-001', 'test');

        $this->assertInstanceOf(CargoForzosoRechazado::class, $result);
    }

    #[Test]
    public function cargo_forzoso_http_422_devuelve_rechazado(): void
    {
        Http::fake(['http://saldo.test/*' => Http::response(['error' => 'validation'], 422)]);

        $client = new SaldoClient();
        $result = $client->cargoForzoso('USR-01', 50.0, 'uuid-001', 'test');

        $this->assertInstanceOf(CargoForzosoRechazado::class, $result);
    }

    #[Test]
    public function cargo_forzoso_http_409_devuelve_desconocido(): void
    {
        Http::fake(['http://saldo.test/*' => Http::response(['error' => 'conflict'], 409)]);

        $client = new SaldoClient();
        $result = $client->cargoForzoso('USR-01', 50.0, 'uuid-001', 'test');

        $this->assertInstanceOf(CargoForzosoDesconocido::class, $result);
    }

    #[Test]
    public function cargo_forzoso_http_500_devuelve_desconocido(): void
    {
        Http::fake(['http://saldo.test/*' => Http::response([], 500)]);

        $client = new SaldoClient();
        $result = $client->cargoForzoso('USR-01', 50.0, 'uuid-001', 'test');

        $this->assertInstanceOf(CargoForzosoDesconocido::class, $result);
    }

    #[Test]
    public function cargo_forzoso_timeout_o_excepcion_devuelve_desconocido(): void
    {
        Http::fake(['http://saldo.test/*' => function () {
            throw new \Illuminate\Http\Client\ConnectionException('timeout');
        }]);

        $client = new SaldoClient();
        $result = $client->cargoForzoso('USR-01', 50.0, 'uuid-001', 'test');

        $this->assertInstanceOf(CargoForzosoDesconocido::class, $result);
    }

    // ─── ReintentaConciliacion — tres ramas ──────────────────────────────────

    #[Test]
    public function job_cargo_cobrado_marca_conciliacion_exitosa(): void
    {
        $conciliacion = $this->crearConciliacion();
        $job = new ReintentaConciliacion($conciliacion);
        $job->handle($this->makeStub(new CargoForzosoCobrado()), new NullPedidoCreator());

        $conciliacion->refresh();
        $this->assertEquals(ConciliacionPendiente::ESTADO_EXITOSA, $conciliacion->estado_conciliacion);

        $carrito = Carrito::where('uuid', $conciliacion->carrito_uuid)->first();
        $this->assertEquals(Carrito::ESTADO_CONFIRMADO, $carrito->estado);
    }

    #[Test]
    public function job_cargo_rechazado_reagenda_como_pendiente(): void
    {
        Queue::fake();

        $conciliacion = $this->crearConciliacion(intentos: 0);
        $job = new ReintentaConciliacion($conciliacion);
        $job->handle($this->makeStub(new CargoForzosoRechazado()), new NullPedidoCreator());

        $conciliacion->refresh();
        $this->assertEquals(ConciliacionPendiente::ESTADO_PENDIENTE, $conciliacion->estado_conciliacion);
        $this->assertEquals(1, $conciliacion->intentos);
        $this->assertNotNull($conciliacion->proximo_intento_at);

        Queue::assertPushed(ReintentaConciliacion::class);
    }

    #[Test]
    public function job_cargo_desconocido_marca_revision_manual_sin_reintentar(): void
    {
        Queue::fake();

        $conciliacion = $this->crearConciliacion();
        $job = new ReintentaConciliacion($conciliacion);
        $job->handle($this->makeStub(new CargoForzosoDesconocido()), new NullPedidoCreator());

        $conciliacion->refresh();
        $this->assertEquals(ConciliacionPendiente::ESTADO_REQUIERE_REVISION, $conciliacion->estado_conciliacion);

        Queue::assertNothingPushed();
    }

    #[Test]
    public function dos_jobs_sobre_misma_conciliacion_solo_uno_ejecuta_cargo(): void
    {
        $contadorCargos = 0;
        $stub = new class($contadorCargos) extends SaldoClient {
            public int $llamadas = 0;
            public function reservar(string $u, float $m, string $c, string $slug, string $concepto): SaldoResult { return new SaldoUnavailable(); }
            public function confirmar(string $reservaId, string $carritoUuid): bool { return true; }
            public function liberar(string $reservaId): bool { return true; }
            public function cargoForzoso(string $u, float $m, string $c, string $concepto, ?string $estado = null): SaldoResult
            {
                $this->llamadas++;
                return new CargoForzosoCobrado();
            }
        };

        $conciliacion = $this->crearConciliacion();

        // Primer job → éxito
        $job1 = new ReintentaConciliacion($conciliacion);
        $job1->handle($stub, new NullPedidoCreator());

        $this->assertEquals(1, $stub->llamadas, 'Primer job debe ejecutar cargoForzoso');
        $this->assertEquals(ConciliacionPendiente::ESTADO_EXITOSA, $conciliacion->fresh()->estado_conciliacion);

        // Segundo job → ve estado EXITOSA (no PENDIENTE) → return silencioso
        $job2 = new ReintentaConciliacion($conciliacion->fresh());
        $job2->handle($stub, new NullPedidoCreator());

        $this->assertEquals(1, $stub->llamadas, 'Segundo job NO debe volver a ejecutar cargoForzoso');
    }

    // ─── ReconciliarSaldoCommand — limpieza TTL ───────────────────────────────

    #[Test]
    public function conciliacion_procesando_vieja_pasa_a_revision_manual(): void
    {
        $conciliacion = $this->crearConciliacion(estado: ConciliacionPendiente::ESTADO_PROCESANDO);

        // Simular que lleva más de 10 minutos en procesando
        \Illuminate\Support\Facades\DB::table('cart_conciliaciones_pendientes')
            ->where('id', $conciliacion->id)
            ->update(['updated_at' => now()->subMinutes(15)]);

        $this->artisan('carrito:reconciliar-saldo')->assertSuccessful();

        $this->assertEquals(
            ConciliacionPendiente::ESTADO_REQUIERE_REVISION,
            $conciliacion->fresh()->estado_conciliacion
        );
    }

    #[Test]
    public function conciliacion_procesando_reciente_se_ignora(): void
    {
        $conciliacion = $this->crearConciliacion(estado: ConciliacionPendiente::ESTADO_PROCESANDO);
        // updated_at reciente (dentro del TTL)

        $this->artisan('carrito:reconciliar-saldo')->assertSuccessful();

        $this->assertEquals(
            ConciliacionPendiente::ESTADO_PROCESANDO,
            $conciliacion->fresh()->estado_conciliacion
        );
    }

    #[Test]
    public function carrito_procesando_checkout_viejo_pasa_a_abierto(): void
    {
        $carrito = Carrito::create([
            'uuid'           => 'uuid-ttl-' . uniqid(),
            'modulo_id'      => $this->modulo->id,
            'usuario_ref'    => 'USR-TTL',
            'estado'         => Carrito::ESTADO_PROCESANDO_CHECKOUT,
            'requiere_saldo' => false,
            'total'          => '0.00',
        ]);

        // Simular que lleva más de 10 minutos en procesando_checkout
        \Illuminate\Support\Facades\DB::table('cart_carritos')
            ->where('id', $carrito->id)
            ->update(['updated_at' => now()->subMinutes(15)]);

        $this->artisan('carrito:reconciliar-saldo')->assertSuccessful();

        $this->assertEquals(Carrito::ESTADO_ABIERTO, $carrito->fresh()->estado);
    }

    #[Test]
    public function carrito_procesando_checkout_reciente_se_ignora(): void
    {
        $carrito = Carrito::create([
            'uuid'           => 'uuid-ttl-reciente-' . uniqid(),
            'modulo_id'      => $this->modulo->id,
            'usuario_ref'    => 'USR-TTL2',
            'estado'         => Carrito::ESTADO_PROCESANDO_CHECKOUT,
            'requiere_saldo' => false,
            'total'          => '0.00',
        ]);
        // updated_at reciente (dentro del TTL) — no se toca

        $this->artisan('carrito:reconciliar-saldo')->assertSuccessful();

        $this->assertEquals(Carrito::ESTADO_PROCESANDO_CHECKOUT, $carrito->fresh()->estado);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function crearConciliacion(int $intentos = 0, string $estado = ConciliacionPendiente::ESTADO_PENDIENTE): ConciliacionPendiente
    {
        $carrito = Carrito::create([
            'uuid'           => 'uuid-conc-' . uniqid(),
            'modulo_id'      => $this->modulo->id,
            'usuario_ref'    => 'USR-CONC-' . uniqid(),
            'estado'         => Carrito::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION,
            'requiere_saldo' => true,
            'total'          => '50.00',
        ]);

        return ConciliacionPendiente::create([
            'carrito_uuid'        => $carrito->uuid,
            'modulo_id'           => $this->modulo->id,
            'usuario_ref'         => $carrito->usuario_ref,
            'monto'               => 50.00,
            'intentos'            => $intentos,
            'estado_conciliacion' => $estado,
        ]);
    }

    private function makeStub(SaldoResult $resultado): SaldoClient
    {
        return new class($resultado) extends SaldoClient {
            public function __construct(private readonly SaldoResult $r) {}
            public function reservar(string $u, float $m, string $c, string $slug, string $concepto): SaldoResult { return new SaldoUnavailable(); }
            public function confirmar(string $reservaId, string $carritoUuid): bool { return true; }
            public function liberar(string $reservaId): bool { return true; }
            public function cargoForzoso(string $u, float $m, string $c, string $concepto, ?string $estado = null): SaldoResult { return $this->r; }
        };
    }
}
