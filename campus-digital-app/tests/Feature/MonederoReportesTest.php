<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SaldoMovimiento;
use App\Models\Modulo;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonederoReportesTest extends TestCase
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

    protected function createMovimientos($usuario = null, $count = 5)
    {
        $usuario = $usuario ?? $this->estudiante;
        
        SaldoMovimiento::factory()->count($count)->create([
            'usuario_id' => $usuario->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);
    }

    /** @test */
    public function test_admin_can_access_reportes_page()
    {
        $this->createMovimientos();

        $response = $this->actingAs($this->admin)
            ->get('/admin/monedero/reportes');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_student_cannot_access_reportes_page()
    {
        $response = $this->actingAs($this->estudiante)
            ->get('/admin/monedero/reportes');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_unauthenticated_cannot_access_reportes()
    {
        $response = $this->get('/admin/monedero/reportes');

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function test_estado_cuenta_api_returns_user_movements()
    {
        $this->createMovimientos($this->estudiante, 5);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/estado-cuenta?usuario_id=' . $this->estudiante->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'usuario_id', 'modulo_id', 'tipo', 'monto', 'descripcion', 'created_at']
            ])
            ->assertJsonCount(5);
    }

    /** @test */
    public function test_estado_cuenta_filters_by_usuario_id()
    {
        $usuario1 = User::factory()->create();
        $usuario2 = User::factory()->create();

        $this->createMovimientos($usuario1, 3);
        $this->createMovimientos($usuario2, 2);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/estado-cuenta?usuario_id=' . $usuario1->id);

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function test_estado_cuenta_filters_by_date_range()
    {
        $startDate = now()->subDays(5)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $this->createMovimientos();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/monedero/reportes/estado-cuenta?usuario_id={$this->estudiante->id}&fecha_inicio={$startDate}&fecha_fin={$endDate}");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_movimientos_api_returns_paginated_list()
    {
        $this->createMovimientos($this->estudiante, 15);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/movimientos?pagina=1&por_pagina=10');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'total', 'per_page', 'current_page']);
    }

    /** @test */
    public function test_movimientos_filters_by_tipo()
    {
        SaldoMovimiento::factory()->count(5)->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);

        SaldoMovimiento::factory()->count(3)->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'abono',
            'monto' => 200
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/movimientos?tipo=cargo');

        $response->assertStatus(200);
        $this->assertGreater($response->json('total'), 0);
    }

    /** @test */
    public function test_movimientos_filters_by_modulo()
    {
        $modulo1 = Modulo::factory()->create(['nombre' => 'Cafetería']);
        $modulo2 = Modulo::factory()->create(['nombre' => 'Copias']);

        SaldoMovimiento::factory()->count(5)->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $modulo1->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);

        SaldoMovimiento::factory()->count(3)->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $modulo2->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/movimientos?modulo_id=' . $modulo1->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_consumo_categoria_api_returns_breakdown()
    {
        $modulo1 = Modulo::factory()->create(['nombre' => 'Cafetería']);
        $modulo2 = Modulo::factory()->create(['nombre' => 'Copias']);

        SaldoMovimiento::factory()->count(4)->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $modulo1->id,
            'tipo' => 'cargo',
            'monto' => 50
        ]);

        SaldoMovimiento::factory()->count(2)->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $modulo2->id,
            'tipo' => 'cargo',
            'monto' => 100
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/consumo-categoria');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['modulo_id', 'nombre', 'total_movimientos', 'monto_total']
            ])
            ->assertJsonCount(2);
    }

    /** @test */
    public function test_consumo_categoria_ignores_deposits()
    {
        SaldoMovimiento::factory()->create([
            'usuario_id' => $this->estudiante->id,
            'modulo_id' => $this->modulo->id,
            'tipo' => 'abono',
            'monto' => 1000
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/consumo-categoria');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    /** @test */
    public function test_export_estado_cuenta_csv()
    {
        $this->createMovimientos($this->estudiante, 5);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/export/estado-cuenta?usuario_id=' . $this->estudiante->id . '&formato=csv');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function test_export_estado_cuenta_pdf()
    {
        $this->createMovimientos($this->estudiante, 5);

        $response = $this->actingAs($this->admin)
            ->get('/api/admin/monedero/reportes/export/estado-cuenta?usuario_id=' . $this->estudiante->id . '&formato=pdf');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_export_movimientos_csv()
    {
        $this->createMovimientos();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/export/movimientos?formato=csv');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function test_export_movimientos_pdf()
    {
        $this->createMovimientos();

        $response = $this->actingAs($this->admin)
            ->get('/api/admin/monedero/reportes/export/movimientos?formato=pdf');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_export_consumo_categoria_csv()
    {
        $this->createMovimientos();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/export/consumo-categoria?formato=csv');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function test_export_consumo_categoria_pdf()
    {
        $this->createMovimientos();

        $response = $this->actingAs($this->admin)
            ->get('/api/admin/monedero/reportes/export/consumo-categoria?formato=pdf');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_empty_estado_cuenta_returns_empty_array()
    {
        $nuevoUsuario = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/estado-cuenta?usuario_id=' . $nuevoUsuario->id);

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    /** @test */
    public function test_movimientos_pagination_works()
    {
        $this->createMovimientos($this->estudiante, 25);

        $page1 = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/movimientos?pagina=1&por_pagina=10');

        $page2 = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reportes/movimientos?pagina=2&por_pagina=10');

        $page1->assertStatus(200)
            ->assertJsonCount(10, 'data');

        $page2->assertStatus(200)
            ->assertJsonCount(10, 'data');
    }

    /** @test */
    public function test_non_admin_cannot_export_reports()
    {
        $this->createMovimientos();

        $response = $this->actingAs($this->estudiante)
            ->getJson('/api/admin/monedero/reportes/export/estado-cuenta?usuario_id=' . $this->estudiante->id . '&formato=csv');

        $response->assertStatus(403);
    }
}
