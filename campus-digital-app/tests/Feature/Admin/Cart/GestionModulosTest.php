<?php

namespace Tests\Feature\Admin\Cart;

use App\Models\Cart\Bitacora;
use App\Models\Cart\ModuloCliente;
use App\Models\Cart\TokenModulo;
use App\Modules\Cart\Services\ModuleTokenService;
use PHPUnit\Framework\Attributes\Test;

class GestionModulosTest extends AdminCartTestCase
{
    // ─── 1. Lista módulos ─────────────────────────────────────────────────────

    #[Test]
    public function admin_lista_modulos_recibe_200_con_estructura(): void
    {
        $admin  = $this->createAdminUser();
        $modulo = $this->createTestModuloCliente();

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get('/admin/cart/modulos');

        $response->assertOk();
        $response->assertJsonPath('component', 'Admin/Cart/ModulosIndex');

        $ids = collect($response->json('props.modulos.data'))->pluck('id')->all();
        $this->assertContains($modulo->id, $ids);
    }

    // ─── 2. Sin rol → 403 ────────────────────────────────────────────────────

    #[Test]
    public function usuario_sin_rol_recibe_403_en_modulos(): void
    {
        $regular = $this->createRegularUser();

        $this->actingAs($regular)
            ->get('/admin/cart/modulos')
            ->assertForbidden();
    }

    // ─── 3. Revocar tokens ────────────────────────────────────────────────────

    #[Test]
    public function revocar_tokens_marca_revocado_y_registra_bitacora(): void
    {
        $admin   = $this->createAdminUser();
        $modulo  = $this->createTestModuloCliente();
        $service = app(ModuleTokenService::class);

        $service->issuePair($modulo);

        $response = $this->actingAs($admin)
            ->post("/admin/cart/modulos/{$modulo->id}/revocar", [
                'motivo' => 'prueba de revocacion',
            ]);

        $response->assertRedirect();

        // Todos los access tokens del módulo deben estar revocados
        $activos = TokenModulo::where('modulo_id', $modulo->id)
            ->where('tipo', TokenModulo::TIPO_ACCESS)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->count();

        $this->assertEquals(0, $activos);

        // Bitácora registra token.revocacion
        $this->assertDatabaseHas('cart_bitacora', [
            'accion'    => Bitacora::ACCION_TOKEN_REVOCACION,
            'modulo_id' => $modulo->id,
        ]);
    }

    #[Test]
    public function revocar_sin_motivo_usa_motivo_manual(): void
    {
        $admin  = $this->createAdminUser();
        $modulo = $this->createTestModuloCliente();
        app(ModuleTokenService::class)->issuePair($modulo);

        $this->actingAs($admin)
            ->post("/admin/cart/modulos/{$modulo->id}/revocar", [])
            ->assertRedirect();

        $this->assertDatabaseHas('cart_tokens_modulo', [
            'modulo_id'        => $modulo->id,
            'motivo_revocacion' => TokenModulo::MOTIVO_MANUAL,
        ]);
    }

    // ─── 4. Forzar refresh ────────────────────────────────────────────────────

    #[Test]
    public function forzar_refresh_revoca_par_viejo_emite_nuevo_y_redirige_a_one_time(): void
    {
        $admin    = $this->createAdminUser();
        $solicitud = $this->crearSolicitudPendiente('biblioteca');
        $modulo   = ModuloCliente::create([
            'solicitud_id'          => $solicitud->id,
            'nombre'                => $solicitud->nombre_modulo,
            'slug'                  => 'bib-' . uniqid(),
            'tipo_modulo'           => 'biblioteca',
            'categorias_autorizadas' => ['prestamo'],
        ]);

        $service = app(ModuleTokenService::class);
        $service->issuePair($modulo);

        // Confirmamos que hay tokens activos antes del refresh
        $activosBefore = TokenModulo::where('modulo_id', $modulo->id)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->count();
        $this->assertEquals(2, $activosBefore);

        $response = $this->actingAs($admin)
            ->post("/admin/cart/modulos/{$modulo->id}/forzar-refresh");

        $response->assertRedirect();
        $this->assertStringContainsString('/token', $response->headers->get('Location'));

        // Par viejo revocado
        $activosTipo = TokenModulo::where('modulo_id', $modulo->id)
            ->where('tipo', TokenModulo::TIPO_ACCESS)
            ->where('estado', TokenModulo::ESTADO_ACTIVO)
            ->count();
        $this->assertEquals(1, $activosTipo); // el nuevo par está activo

        // Bitácora registra rotacion_admin
        $this->assertDatabaseHas('cart_bitacora', [
            'accion'    => 'token.rotacion_admin',
            'modulo_id' => $modulo->id,
        ]);
    }

    // ─── 5. Access viejo no funciona tras forzar refresh ─────────────────────

    #[Test]
    public function access_viejo_es_rechazado_tras_forzar_refresh(): void
    {
        $admin    = $this->createAdminUser();
        $solicitud = $this->crearSolicitudPendiente('cafeteria');
        $modulo   = ModuloCliente::create([
            'solicitud_id'          => $solicitud->id,
            'nombre'                => 'Cafetería Test',
            'slug'                  => 'caf-' . uniqid(),
            'tipo_modulo'           => 'cafeteria',
            'categorias_autorizadas' => ['prestamo'],
        ]);

        $service = app(ModuleTokenService::class);
        $pair    = $service->issuePair($modulo);
        $oldAccessToken = $pair['access_token'];

        // Forzar refresh (revoca el par viejo)
        $this->actingAs($admin)
            ->post("/admin/cart/modulos/{$modulo->id}/forzar-refresh");

        // El token viejo debe fallar al validar (estado revocado)
        $this->expectException(\RuntimeException::class);
        $service->validate($oldAccessToken);
    }

    // ─── 6. One-time post-forzar-refresh ─────────────────────────────────────

    #[Test]
    public function one_time_tras_forzar_refresh_primera_visita_muestra_tokens(): void
    {
        $admin    = $this->createAdminUser();
        $solicitud = $this->crearSolicitudPendiente('biblioteca');
        $modulo   = ModuloCliente::create([
            'solicitud_id'          => $solicitud->id,
            'nombre'                => 'Biblioteca Test',
            'slug'                  => 'bib-' . uniqid(),
            'tipo_modulo'           => 'biblioteca',
            'categorias_autorizadas' => ['prestamo'],
        ]);
        // TokenEntregaController requires estado=APROBADA to find the solicitud.
        $solicitud->update(['estado' => \App\Models\Cart\SolicitudModulo::ESTADO_APROBADA]);
        app(ModuleTokenService::class)->issuePair($modulo);

        $redirect = $this->actingAs($admin)
            ->post("/admin/cart/modulos/{$modulo->id}/forzar-refresh");
        $tokenUrl = $redirect->headers->get('Location');

        // Primera visita
        $response = $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get($tokenUrl);

        $response->assertOk();
        $this->assertNotNull($response->json('props.accessToken'));
        $this->assertFalse($response->json('props.alreadyDelivered'));

        // Segunda visita
        $response2 = $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get($tokenUrl);

        $response2->assertOk();
        $this->assertNull($response2->json('props.accessToken'));
        $this->assertTrue($response2->json('props.alreadyDelivered'));
    }

    // ─── 7. Bitácora sin filtros ──────────────────────────────────────────────

    #[Test]
    public function bitacora_sin_filtros_pagina_de_25(): void
    {
        $admin = $this->createAdminUser();

        // Insertar 30 entradas
        for ($i = 0; $i < 30; $i++) {
            Bitacora::create([
                'accion'     => 'test.evento',
                'modulo_id'  => null,
                'user_id'    => $admin->id,
                'ip_address' => '127.0.0.1',
                'payload'    => [],
            ]);
        }

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get('/admin/cart/bitacora');

        $response->assertOk();
        $data = $response->json('props.bitacoras.data');
        $this->assertCount(25, $data);
        $this->assertEquals(2, $response->json('props.bitacoras.last_page'));
    }

    // ─── 8. Bitácora filtrada por accion ─────────────────────────────────────

    #[Test]
    public function bitacora_filtrada_por_accion_retorna_solo_coincidencias(): void
    {
        $admin  = $this->createAdminUser();
        $modulo = $this->createTestModuloCliente();

        Bitacora::create(['accion' => 'token.emision',     'modulo_id' => $modulo->id, 'ip_address' => '127.0.0.1', 'payload' => []]);
        Bitacora::create(['accion' => 'token.emision',     'modulo_id' => $modulo->id, 'ip_address' => '127.0.0.1', 'payload' => []]);
        Bitacora::create(['accion' => 'token.revocacion',  'modulo_id' => $modulo->id, 'ip_address' => '127.0.0.1', 'payload' => []]);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get('/admin/cart/bitacora?accion[]=token.emision');

        $response->assertOk();
        $acciones = collect($response->json('props.bitacoras.data'))->pluck('accion')->unique()->all();
        $this->assertEquals(['token.emision'], $acciones);
        $this->assertCount(2, $response->json('props.bitacoras.data'));
    }

    // ─── 9. Bitácora filtrada por rango de fechas ─────────────────────────────

    #[Test]
    public function bitacora_filtrada_por_rango_de_fechas(): void
    {
        $admin  = $this->createAdminUser();
        $modulo = $this->createTestModuloCliente();

        // Use DB::table to bypass Eloquent auto-timestamps so we can set exact dates.
        \Illuminate\Support\Facades\DB::table('cart_bitacora')->insert([
            ['accion' => 'test.evento', 'modulo_id' => $modulo->id, 'ip_address' => '127.0.0.1', 'payload' => '[]', 'created_at' => '2026-01-15 10:00:00'],
            ['accion' => 'test.evento', 'modulo_id' => $modulo->id, 'ip_address' => '127.0.0.1', 'payload' => '[]', 'created_at' => '2026-03-10 10:00:00'],
        ]);

        $response = $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get('/admin/cart/bitacora?desde=2026-03-01&hasta=2026-03-31');

        $response->assertOk();
        $this->assertCount(1, $response->json('props.bitacoras.data'));
        $this->assertStringContainsString('2026-03', $response->json('props.bitacoras.data.0.created_at'));
    }
}
