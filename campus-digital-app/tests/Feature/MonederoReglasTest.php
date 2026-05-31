<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SaldoRegla;
use App\Models\Modulo;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonederoReglasTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $estudiante;
    protected User $targetUsuario;
    protected Modulo $modulo;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        $this->estudiante = User::factory()->create();
        $this->targetUsuario = User::factory()->create();
        $this->modulo = Modulo::factory()->create(['nombre' => 'Cafetería']);
    }

    /** @test */
    public function test_admin_can_access_reglas_page()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monedero/reglas');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_student_cannot_access_reglas_page()
    {
        $response = $this->actingAs($this->estudiante)
            ->get('/admin/monedero/reglas');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_unauthenticated_cannot_access_reglas()
    {
        $response = $this->get('/admin/monedero/reglas');

        $response->assertStatus(302)
            ->assertRedirect('/login');
    }

    /** @test */
    public function test_get_reglas_returns_paginated_list()
    {
        SaldoRegla::factory()->count(15)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reglas?pagina=1&por_pagina=10');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'total', 'per_page', 'current_page']);
    }

    /** @test */
    public function test_get_reglas_with_no_rules()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reglas');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'total'])
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function test_create_daily_rule()
    {
        $data = [
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'diario',
            'monto_maximo' => 500,
            'modulo_id' => $this->modulo->id,
            'descripcion' => 'Límite diario para cafetería'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/monedero/reglas', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'usuario_id', 'tipo_limite', 'monto_maximo']);

        $this->assertDatabaseHas('saldo_reglas', [
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'diario',
            'monto_maximo' => 500
        ]);
    }

    /** @test */
    public function test_create_weekly_rule()
    {
        $data = [
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'semanal',
            'monto_maximo' => 2000,
            'modulo_id' => $this->modulo->id,
            'descripcion' => 'Límite semanal'
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/monedero/reglas', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('saldo_reglas', [
            'tipo_limite' => 'semanal',
            'monto_maximo' => 2000
        ]);
    }

    /** @test */
    public function test_create_monthly_rule()
    {
        $data = [
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'mensual',
            'monto_maximo' => 8000,
            'modulo_id' => $this->modulo->id
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/monedero/reglas', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('saldo_reglas', [
            'tipo_limite' => 'mensual',
            'monto_maximo' => 8000
        ]);
    }

    /** @test */
    public function test_create_rule_validates_monto_positivo()
    {
        $data = [
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'diario',
            'monto_maximo' => -100,
            'modulo_id' => $this->modulo->id
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/monedero/reglas', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('monto_maximo');
    }

    /** @test */
    public function test_create_rule_validates_tipo_limite()
    {
        $data = [
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'invalido',
            'monto_maximo' => 500,
            'modulo_id' => $this->modulo->id
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/monedero/reglas', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('tipo_limite');
    }

    /** @test */
    public function test_create_rule_requires_usuario_id()
    {
        $data = [
            'tipo_limite' => 'diario',
            'monto_maximo' => 500,
            'modulo_id' => $this->modulo->id
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/monedero/reglas', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('usuario_id');
    }

    /** @test */
    public function test_update_rule()
    {
        $regla = SaldoRegla::factory()->create([
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'diario',
            'monto_maximo' => 500
        ]);

        $data = [
            'monto_maximo' => 750,
            'descripcion' => 'Límite aumentado'
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/monedero/reglas/{$regla->id}", $data);

        $response->assertStatus(200)
            ->assertJson(['monto_maximo' => 750]);

        $this->assertDatabaseHas('saldo_reglas', [
            'id' => $regla->id,
            'monto_maximo' => 750
        ]);
    }

    /** @test */
    public function test_update_rule_validates_monto()
    {
        $regla = SaldoRegla::factory()->create();

        $data = [
            'monto_maximo' => -200
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/monedero/reglas/{$regla->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('monto_maximo');
    }

    /** @test */
    public function test_delete_rule()
    {
        $regla = SaldoRegla::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/monedero/reglas/{$regla->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('saldo_reglas', ['id' => $regla->id]);
    }

    /** @test */
    public function test_restore_soft_deleted_rule()
    {
        $regla = SaldoRegla::factory()->create();
        $regla->delete();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/monedero/reglas/{$regla->id}/restore");

        $response->assertStatus(200);

        $this->assertNotSoftDeleted('saldo_reglas', ['id' => $regla->id]);
    }

    /** @test */
    public function test_show_single_rule()
    {
        $regla = SaldoRegla::factory()->create([
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'diario',
            'monto_maximo' => 500
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/monedero/reglas/{$regla->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $regla->id,
                'usuario_id' => $this->targetUsuario->id,
                'tipo_limite' => 'diario',
                'monto_maximo' => 500
            ]);
    }

    /** @test */
    public function test_show_nonexistent_rule_returns_404()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reglas/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_get_reglas_page_shows_form_link()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monedero/reglas');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_create_regla_page_loads()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/monedero/reglas/crear');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_edit_regla_page_loads()
    {
        $regla = SaldoRegla::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/monedero/reglas/{$regla->id}/editar");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_multiple_rules_for_same_user_different_types()
    {
        SaldoRegla::factory()->create([
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'diario',
            'monto_maximo' => 500
        ]);

        SaldoRegla::factory()->create([
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'semanal',
            'monto_maximo' => 2000
        ]);

        SaldoRegla::factory()->create([
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'mensual',
            'monto_maximo' => 8000
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/monedero/reglas');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function test_rule_scope_diario()
    {
        $regla = SaldoRegla::factory()->create(['tipo_limite' => 'diario']);
        $otherRegla = SaldoRegla::factory()->create(['tipo_limite' => 'semanal']);

        $result = SaldoRegla::diario()->get();

        $this->assertTrue($result->contains('id', $regla->id));
        $this->assertFalse($result->contains('id', $otherRegla->id));
    }

    /** @test */
    public function test_rule_scope_semanal()
    {
        $regla = SaldoRegla::factory()->create(['tipo_limite' => 'semanal']);
        $otherRegla = SaldoRegla::factory()->create(['tipo_limite' => 'diario']);

        $result = SaldoRegla::semanal()->get();

        $this->assertTrue($result->contains('id', $regla->id));
        $this->assertFalse($result->contains('id', $otherRegla->id));
    }

    /** @test */
    public function test_rule_scope_mensual()
    {
        $regla = SaldoRegla::factory()->create(['tipo_limite' => 'mensual']);
        $otherRegla = SaldoRegla::factory()->create(['tipo_limite' => 'diario']);

        $result = SaldoRegla::mensual()->get();

        $this->assertTrue($result->contains('id', $regla->id));
        $this->assertFalse($result->contains('id', $otherRegla->id));
    }

    /** @test */
    public function test_rule_scope_activas()
    {
        $activaRegla = SaldoRegla::factory()->create();
        $deletedRegla = SaldoRegla::factory()->create();
        $deletedRegla->delete();

        $result = SaldoRegla::activas()->get();

        $this->assertTrue($result->contains('id', $activaRegla->id));
        $this->assertFalse($result->contains('id', $deletedRegla->id));
    }

    /** @test */
    public function test_non_admin_cannot_create_rule()
    {
        $data = [
            'usuario_id' => $this->targetUsuario->id,
            'tipo_limite' => 'diario',
            'monto_maximo' => 500,
            'modulo_id' => $this->modulo->id
        ];

        $response = $this->actingAs($this->estudiante)
            ->postJson('/api/admin/monedero/reglas', $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_non_admin_cannot_update_rule()
    {
        $regla = SaldoRegla::factory()->create();

        $response = $this->actingAs($this->estudiante)
            ->putJson("/api/admin/monedero/reglas/{$regla->id}", ['monto_maximo' => 750]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_non_admin_cannot_delete_rule()
    {
        $regla = SaldoRegla::factory()->create();

        $response = $this->actingAs($this->estudiante)
            ->deleteJson("/api/admin/monedero/reglas/{$regla->id}");

        $response->assertStatus(403);
    }
}
