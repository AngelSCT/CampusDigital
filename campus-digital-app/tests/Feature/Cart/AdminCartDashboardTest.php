<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Carrito;
use App\Modules\Cart\Services\CartDashboardService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests de CartDashboardService sobre tablas cart_* en SQLite in-memory.
 * No hace peticiones HTTP — prueba el servicio directamente para evitar
 * dependencias del sistema de auth/roles en CartTestCase.
 */
class AdminCartDashboardTest extends CartTestCase
{
    private \App\Models\Cart\ModuloCliente $modulo;
    private CartDashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();

        $this->modulo  = $this->createTestModuloCliente([
            'slug'                   => 'dashboard-test',
            'tipo_modulo'            => 'test',
            'categorias_autorizadas' => [],
        ]);

        $this->service = new CartDashboardService();
    }

    // ─── TEST 1: Conteos correctos ────────────────────────────────────────────

    #[Test]
    public function test1_cuenta_completados_y_abandonados_correctamente(): void
    {
        // 3 confirmados
        for ($i = 0; $i < 3; $i++) {
            $this->crearCarritoConEstado(Carrito::ESTADO_CONFIRMADO, total: 20.00);
        }
        // 2 cancelados
        for ($i = 0; $i < 2; $i++) {
            $this->crearCarritoConEstado(Carrito::ESTADO_CANCELADO);
        }
        // 1 expirado
        $this->crearCarritoConEstado(Carrito::ESTADO_EXPIRADO);
        // 1 abierto
        $this->crearCarritoConEstado(Carrito::ESTADO_ABIERTO);

        $data = $this->service->resumen();

        $this->assertEquals(3, $data['checkouts_completados']);
        $this->assertEquals(3, $data['abandonados'], 'cancelados(2) + expirados(1) = 3');
        $this->assertEquals(0, $data['confirmados_pendientes']);
    }

    // ─── TEST 2: Consumo promedio correcto ────────────────────────────────────

    #[Test]
    public function test2_consumo_promedio_correcto(): void
    {
        foreach ([10.00, 20.00, 30.00] as $total) {
            $this->crearCarritoConEstado(Carrito::ESTADO_CONFIRMADO, total: $total);
        }

        $data = $this->service->resumen();

        $this->assertEquals(20.00, $data['consumo_promedio'],
            'Promedio de 10+20+30 = 20.00');
        $this->assertEquals(60.00, $data['total_recaudado'],
            'Suma de 10+20+30 = 60.00');
    }

    // ─── TEST 3: Agrupación por horario ───────────────────────────────────────

    #[Test]
    public function test3_agrupacion_por_horario(): void
    {
        // 2 checkouts a las 10:xx
        $this->crearCarritoConfirmadoAHora(10);
        $this->crearCarritoConfirmadoAHora(10);
        // 1 checkout a las 15:xx
        $this->crearCarritoConfirmadoAHora(15);

        $horario = $this->service->consumoPorHorario();

        $this->assertEquals(2, $horario[10] ?? 0,
            'Hora 10 debe tener 2 checkouts');
        $this->assertEquals(1, $horario[15] ?? 0,
            'Hora 15 debe tener 1 checkout');
        $this->assertArrayNotHasKey(0, $horario,
            'Horas sin actividad no deben aparecer');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function crearCarritoConEstado(string $estado, float $total = 0.00): Carrito
    {
        $confirmedAt = $estado === Carrito::ESTADO_CONFIRMADO ? now() : null;

        return Carrito::create([
            'uuid'           => (string) \Illuminate\Support\Str::uuid(),
            'modulo_id'      => $this->modulo->id,
            'usuario_ref'    => '1',
            'estado'         => $estado,
            'requiere_saldo' => false,
            'total'          => $total,
            'confirmed_at'   => $confirmedAt,
        ]);
    }

    private function crearCarritoConfirmadoAHora(int $hora): Carrito
    {
        return Carrito::create([
            'uuid'           => (string) \Illuminate\Support\Str::uuid(),
            'modulo_id'      => $this->modulo->id,
            'usuario_ref'    => '1',
            'estado'         => Carrito::ESTADO_CONFIRMADO,
            'requiere_saldo' => false,
            'total'          => 10.00,
            'confirmed_at'   => Carbon::today()->setHour($hora)->setMinute(30),
        ]);
    }
}
