<?php

namespace Tests\Feature\Admin\Cart;

use App\Models\Cart\Bitacora;
use App\Models\Cart\ModuloCliente;
use App\Models\Cart\SolicitudModulo;
use App\Models\Cart\TokenModulo;
use PHPUnit\Framework\Attributes\Test;

class SolicitudAprobacionTest extends AdminCartTestCase
{
    // ─── Aprobación ──────────────────────────────────────────────────────────

    #[Test]
    public function admin_con_rol_aprueba_solicitud_genera_tokens(): void
    {
        $admin    = $this->createAdminUser();
        $solicitud = $this->crearSolicitudPendiente('biblioteca');

        $response = $this->actingAs($admin)
            ->post("/admin/cart/solicitudes/{$solicitud->id}/aprobar");

        // Redirige a la pantalla one-time
        $response->assertRedirect();
        $this->assertStringContainsString('/token', $response->headers->get('Location'));

        // Solicitud actualizada
        $solicitud->refresh();
        $this->assertEquals(SolicitudModulo::ESTADO_APROBADA, $solicitud->estado);
        $this->assertEquals($admin->id, $solicitud->revisado_por);

        // ModuloCliente creado
        $modulo = ModuloCliente::where('solicitud_id', $solicitud->id)->first();
        $this->assertNotNull($modulo);
        $this->assertEquals('biblioteca', $modulo->tipo_modulo);

        // Dos tokens emitidos (access + refresh)
        $this->assertEquals(2, TokenModulo::where('modulo_id', $modulo->id)->count());

        // Bitácora registra token.emision
        $this->assertDatabaseHas('cart_bitacora', [
            'accion'    => Bitacora::ACCION_TOKEN_EMISION,
            'modulo_id' => $modulo->id,
        ]);
    }

    #[Test]
    public function admin_sin_rol_intenta_aprobar_recibe_403(): void
    {
        $regular  = $this->createRegularUser();
        $solicitud = $this->crearSolicitudPendiente('cafeteria');

        $this->actingAs($regular)
            ->post("/admin/cart/solicitudes/{$solicitud->id}/aprobar")
            ->assertForbidden();

        // La solicitud no debe haber cambiado
        $this->assertEquals(SolicitudModulo::ESTADO_PENDIENTE, $solicitud->fresh()->estado);
    }

    // ─── Rechazo ─────────────────────────────────────────────────────────────

    #[Test]
    public function admin_rechaza_con_motivo_valido(): void
    {
        $admin    = $this->createAdminUser();
        $solicitud = $this->crearSolicitudPendiente('copias');

        $this->actingAs($admin)
            ->post("/admin/cart/solicitudes/{$solicitud->id}/rechazar", [
                'motivo_rechazo' => 'El módulo ya existe con otro nombre registrado.',
            ])
            ->assertRedirect('/admin/cart/solicitudes');

        $solicitud->refresh();
        $this->assertEquals(SolicitudModulo::ESTADO_RECHAZADA, $solicitud->estado);
        $this->assertStringContainsString('ya existe', $solicitud->motivo_rechazo);
        $this->assertEquals($admin->id, $solicitud->revisado_por);

        $this->assertDatabaseHas('cart_bitacora', ['accion' => 'modulo.solicitud_rechazada']);
    }

    #[Test]
    public function admin_rechaza_sin_motivo_recibe_422(): void
    {
        $admin    = $this->createAdminUser();
        $solicitud = $this->crearSolicitudPendiente('souvenirs');

        // postJson añade Accept: application/json → Laravel devuelve 422 en vez de redirigir.
        $this->actingAs($admin)
            ->postJson("/admin/cart/solicitudes/{$solicitud->id}/rechazar", [])
            ->assertStatus(422);

        $this->assertEquals(SolicitudModulo::ESTADO_PENDIENTE, $solicitud->fresh()->estado);
    }

    // ─── One-time display ────────────────────────────────────────────────────

    #[Test]
    public function pantalla_one_time_muestra_jwts_primera_vez_y_marca_entregado_at(): void
    {
        $admin    = $this->createAdminUser();
        $solicitud = $this->crearSolicitudPendiente('biblioteca');

        // 1. Aprobar → flash jwt_pair + redirect a /token
        $redirect = $this->actingAs($admin)
            ->post("/admin/cart/solicitudes/{$solicitud->id}/aprobar");

        $redirect->assertRedirect();
        $tokenUrl = $redirect->headers->get('Location');

        // 2. Primera visita a la pantalla one-time (lee el flash)
        $response = $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get($tokenUrl);

        $response->assertOk();
        $this->assertNotNull($response->json('props.accessToken'));
        $this->assertNotNull($response->json('props.refreshToken'));
        $this->assertFalse($response->json('props.alreadyDelivered'));

        // entregado_at marcado en BD
        $modulo = ModuloCliente::where('solicitud_id', $solicitud->id)->first();
        $token  = TokenModulo::where('modulo_id', $modulo->id)
            ->where('tipo', TokenModulo::TIPO_ACCESS)
            ->first();
        $this->assertNotNull($token->entregado_at);

        // Bitácora registra token.visualizado
        $this->assertDatabaseHas('cart_bitacora', ['accion' => Bitacora::ACCION_TOKEN_VISUALIZADO]);
    }

    #[Test]
    public function segunda_visita_one_time_no_muestra_jwts(): void
    {
        $admin    = $this->createAdminUser();
        $solicitud = $this->crearSolicitudPendiente('biblioteca');

        // Aprobar
        $redirect = $this->actingAs($admin)
            ->post("/admin/cart/solicitudes/{$solicitud->id}/aprobar");
        $tokenUrl = $redirect->headers->get('Location');

        // Primera visita: consume el flash y marca entregado_at
        $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get($tokenUrl)
            ->assertOk()
            ->assertJsonPath('props.alreadyDelivered', false);

        // Segunda visita: entregado_at != null → already delivered
        $response = $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get($tokenUrl);

        $response->assertOk();
        $this->assertNull($response->json('props.accessToken'));
        $this->assertNull($response->json('props.refreshToken'));
        $this->assertTrue($response->json('props.alreadyDelivered'));
    }

    #[Test]
    public function cada_visita_one_time_registra_token_visualizado_en_bitacora(): void
    {
        $admin    = $this->createAdminUser();
        $solicitud = $this->crearSolicitudPendiente('biblioteca');

        $redirect = $this->actingAs($admin)
            ->post("/admin/cart/solicitudes/{$solicitud->id}/aprobar");
        $tokenUrl = $redirect->headers->get('Location');

        // Primera visita
        $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get($tokenUrl)
            ->assertOk();

        // Segunda visita
        $this->actingAs($admin)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get($tokenUrl)
            ->assertOk();

        // Dos registros de 'token.visualizado'
        $count = \App\Models\Cart\Bitacora::where('accion', Bitacora::ACCION_TOKEN_VISUALIZADO)->count();
        $this->assertEquals(2, $count);
    }

    #[Test]
    public function usuario_sin_rol_recibe_403_al_entrar_al_panel(): void
    {
        $regular = $this->createRegularUser();

        $this->actingAs($regular)
            ->get('/admin/cart/solicitudes')
            ->assertForbidden();
    }
}
