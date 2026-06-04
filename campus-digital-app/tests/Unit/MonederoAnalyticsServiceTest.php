<?php

namespace Tests\Unit;

use App\Models\SaldoMovimiento;
use App\Models\Modulo;
use App\Models\User;
use App\Services\MonederoAnalyticsService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonederoAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MonederoAnalyticsService $analyticsService;
    protected User $usuario;
    protected Modulo $modulo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = app(MonederoAnalyticsService::class);
    }

    protected function createTestData()
    {
        $this->usuario = User::factory()->create();
        $this->modulo = Modulo::factory()->create([
            'nombre' => 'Cafetería',
            'descripcion' => 'Módulo de cafetería del campus'
        ]);
    }

    /** @test */
    public function test_get_kpis_calculates_total_charges()
    {
        $this->createTestData();
        
        SaldoMovimiento::factory()->count(3)->create([
            'usuario_id' => $this->usuario->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);

        $kpis = $this->analyticsService->getKPIs();

        $this->assertEquals(300, $kpis['totalCargos']);
    }

    /** @test */
    public function test_get_kpis_calculates_total_deposits()
    {
        $this->createTestData();
        
        SaldoMovimiento::factory()->count(2)->create([
            'usuario_id' => $this->usuario->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'abono',
            'monto' => 150
        ]);

        $kpis = $this->analyticsService->getKPIs();

        $this->assertEquals(300, $kpis['totalAbonos']);
    }

    /** @test */
    public function test_get_kpis_calculates_balance()
    {
        $this->createTestData();
        
        SaldoMovimiento::factory()->create([
            'usuario_id' => $this->usuario->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'abono',
            'monto' => 1000
        ]);

        SaldoMovimiento::factory()->create([
            'usuario_id' => $this->usuario->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'cargo',
            'monto' => 300
        ]);

        $kpis = $this->analyticsService->getKPIs();

        $this->assertEquals(700, $kpis['saldoNeto']);
    }

    /** @test */
    public function test_get_top_usuarios_returns_users_with_highest_spending()
    {
        $usuario1 = User::factory()->create();
        $usuario2 = User::factory()->create();
        $usuario3 = User::factory()->create();
        $modulo = Modulo::factory()->create();

        SaldoMovimiento::factory()->count(5)->create([
            'usuario_id' => $usuario1->id,
            'modulo_id' => $modulo->id,
            'tipo' => 'cargo',
            'monto' => 200
        ]);

        SaldoMovimiento::factory()->count(2)->create([
            'usuario_id' => $usuario2->id,
            'modulo_id' => $modulo->id,
            'tipo' => 'cargo',
            'monto' => 150
        ]);

        SaldoMovimiento::factory()->create([
            'usuario_id' => $usuario3->id,
            'modulo_id' => $modulo->id,
            'tipo' => 'cargo',
            'monto' => 50
        ]);

        $topUsuarios = $this->analyticsService->getTopUsuarios(5);

        $this->assertCount(3, $topUsuarios);
        $this->assertEquals($usuario1->id, $topUsuarios[0]['usuario_id']);
        $this->assertEquals(1000, $topUsuarios[0]['total_gastado']);
    }

    /** @test */
    public function test_get_top_usuarios_respects_limit()
    {
        $usuarios = User::factory()->count(10)->create();
        $modulo = Modulo::factory()->create();

        foreach ($usuarios as $index => $usuario) {
            SaldoMovimiento::factory()->count($index + 1)->create([
                'usuario_id' => $usuario->id,
                'modulo_id' => $modulo->id,
                'tipo' => 'cargo',
                'monto' => 100
            ]);
        }

        $topUsuarios = $this->analyticsService->getTopUsuarios(3);

        $this->assertCount(3, $topUsuarios);
    }

    /** @test */
    public function test_get_consumption_by_module()
    {
        $modulo1 = Modulo::factory()->create(['nombre' => 'Cafetería']);
        $modulo2 = Modulo::factory()->create(['nombre' => 'Copias']);
        $usuario = User::factory()->create();

        SaldoMovimiento::factory()->count(4)->create([
            'usuario_id' => $usuario->id,
            'modulo_id' => $modulo1->id,
            'tipo' => 'cargo',
            'monto' => 50
        ]);

        SaldoMovimiento::factory()->count(2)->create([
            'usuario_id' => $usuario->id,
            'modulo_id' => $modulo2->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);

        $consumption = $this->analyticsService->getConsumoPorCategoria();

        $this->assertCount(2, $consumption);
        $this->assertEquals(200, $consumption->firstWhere('modulo_id', $modulo1->id)['total']);
        $this->assertEquals(200, $consumption->firstWhere('modulo_id', $modulo2->id)['total']);
    }

    /** @test */
    public function test_get_time_series_data_returns_daily_aggregates()
    {
        $usuario = User::factory()->create();
        $modulo = Modulo::factory()->create();
        $today = now();

        SaldoMovimiento::factory()->count(3)->create([
            'usuario_id' => $usuario->id,
            'modulo_id' => $modulo->id,
            'tipo' => 'cargo',
            'monto' => 50,
            'created_at' => $today->copy()->setHour(9)
        ]);

        SaldoMovimiento::factory()->count(2)->create([
            'usuario_id' => $usuario->id,
            'modulo_id' => $modulo->id,
            'tipo' => 'cargo',
            'monto' => 100,
            'created_at' => $today->copy()->setHour(14)
        ]);

        $series = $this->analyticsService->getTimeSeriesData(30);

        $this->assertIsArray($series);
        $this->assertGreater(count($series), 0);
    }

    /** @test */
    public function test_get_time_series_returns_empty_when_no_data()
    {
        $series = $this->analyticsService->getTimeSeriesData(30);

        $this->assertIsArray($series);
        $this->assertEmpty($series);
    }

    /** @test */
    public function test_get_kpis_with_empty_database()
    {
        $kpis = $this->analyticsService->getKPIs();

        $this->assertEquals(0, $kpis['totalCargos']);
        $this->assertEquals(0, $kpis['totalAbonos']);
        $this->assertEquals(0, $kpis['saldoNeto']);
    }

    /** @test */
    public function test_get_top_usuarios_with_empty_database()
    {
        $topUsuarios = $this->analyticsService->getTopUsuarios(5);

        $this->assertIsArray($topUsuarios);
        $this->assertEmpty($topUsuarios);
    }

    /** @test */
    public function test_consumption_by_category_ignores_deposits()
    {
        $modulo = Modulo::factory()->create();
        $usuario = User::factory()->create();

        SaldoMovimiento::factory()->create([
            'usuario_id' => $usuario->id,
            'modulo_id' => $modulo->id,
            'tipo' => 'abono',
            'monto' => 1000
        ]);

        $consumption = $this->analyticsService->getConsumoPorCategoria();

        $this->assertEmpty($consumption);
    }

    /** @test */
    public function test_kpis_counts_only_charges_as_consumption()
    {
        $usuario = User::factory()->create();
        $modulo = Modulo::factory()->create();

        SaldoMovimiento::factory()->count(5)->create([
            'usuario_id' => $usuario->id,
            'modulo_id' => $modulo->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);

        SaldoMovimiento::factory()->count(3)->create([
            'usuario_id' => $usuario->id,
            'modulo_id' => $modulo->id,
            'tipo' => 'abono',
            'monto' => 500
        ]);

        $kpis = $this->analyticsService->getKPIs();

        $this->assertEquals(500, $kpis['totalCargos']);
        $this->assertEquals(1500, $kpis['totalAbonos']);
    }
}
