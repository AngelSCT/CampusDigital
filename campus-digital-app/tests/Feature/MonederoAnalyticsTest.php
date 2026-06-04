<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SaldoMovimiento;
use App\Models\Modulo;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonederoAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $estudiante;
    protected Modulo $modulo;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $this->estudiante = User::factory()->create();
        $this->modulo = Modulo::factory()->create(['nombre' => 'Cafetería']);
    }

    protected function createMovimientos($count = 5)
    {
        SaldoMovimiento::factory()->count($count)->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);
    }

    /** @test */
    public function test_admin_can_access_dashboard_api_endpoint()
    {
        $this->createMovimientos(3);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/kpis');

        $response->assertStatus(200)
            ->assertJsonStructure(['totalCargos', 'totalAbonos', 'saldoNeto']);
    }

    /** @test */
    public function test_non_admin_cannot_access_dashboard_api()
    {
        $response = $this->actingAs($this->estudiante)
            ->getJson('/api/admin/monedero/analytics/kpis');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_kpis_endpoint_returns_correct_totals()
    {
        SaldoMovimiento::factory()->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'cargo',
            'monto' => 150
        ]);

        SaldoMovimiento::factory()->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'abono',
            'monto' => 200
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/kpis');

        $response->assertStatus(200)
            ->assertJson([
                'totalCargos' => 150,
                'totalAbonos' => 200,
                'saldoNeto' => 50
            ]);
    }

    /** @test */
    public function test_top_usuarios_endpoint_returns_paginated_list()
    {
        $this->createMovimientos(10);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/top-usuarios?limit=5');

        $response->assertStatus(200)
            ->assertJsonCount(1) // Only 1 user created movimientos
            ->assertJsonStructure([
                '*' => ['usuario_id', 'nombre', 'email', 'total_gastado', 'transacciones']
            ]);
    }

    /** @test */
    public function test_top_usuarios_respects_limit_parameter()
    {
        $usuarios = User::factory()->count(5)->create();
        
        foreach ($usuarios as $usuario) {
            SaldoMovimiento::factory()->count(3)->create([
                'usuario_id' => $usuario->id,
                'modulo_id' => $this->modulo->id,
                'tipo' => 'cargo',
                'monto' => 50
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/top-usuarios?limit=3');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function test_consumo_por_categoria_endpoint_returns_breakdown()
    {
        $modulo1 = Modulo::factory()->create(['nombre' => 'Cafetería']);
        $modulo2 = Modulo::factory()->create(['nombre' => 'Copias']);
        
        SaldoMovimiento::factory()->count(4)->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $modulo1->id,
            'tipo' => 'cargo',
            'monto' => 50
        ]);

        SaldoMovimiento::factory()->count(3)->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $modulo2->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/consumo-categoria');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => ['modulo_id', 'nombre', 'total', 'transacciones']
            ]);
    }

    /** @test */
    public function test_time_series_endpoint_returns_chart_data()
    {
        $this->createMovimientos(7);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/time-series?dias=30');

        $response->assertStatus(200)
            ->assertJsonStructure(['labels', 'datasets']);
    }

    /** @test */
    public function test_time_series_accepts_days_parameter()
    {
        $this->createMovimientos(3);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/time-series?dias=7');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_dashboard_page_loads_with_auth()
    {
        $this->createMovimientos(5);

        $response = $this->actingAs($this->admin)
            ->get('/admin/monedero/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_student_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->estudiante)
            ->get('/admin/monedero/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get('/admin/monedero/dashboard');

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function test_kpis_with_no_movements_returns_zeros()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/kpis');

        $response->assertStatus(200)
            ->assertJson([
                'totalCargos' => 0,
                'totalAbonos' => 0,
                'saldoNeto' => 0
            ]);
    }

    /** @test */
    public function test_top_usuarios_with_no_movements()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/top-usuarios');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    /** @test */
    public function test_consumo_por_categoria_with_no_movements()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/consumo-categoria');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    /** @test */
    public function test_time_series_with_no_movements()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/analytics/time-series?dias=30');

        $response->assertStatus(200)
            ->assertJson(['labels' => [], 'datasets' => []]);
    }
}
