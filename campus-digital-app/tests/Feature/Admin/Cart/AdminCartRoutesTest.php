<?php

namespace Tests\Feature\Admin\Cart;

use App\Models\Cart\ModuloCliente;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests de acceso a las rutas del panel admin del Módulo Carrito.
 *
 * Verifica:
 *  - Admin autenticado (rol admin_carrito) puede abrir cada ruta y recibe 200.
 *  - Usuario sin autenticación recibe redirección (302) a login.
 *  - Usuario autenticado sin rol admin_carrito recibe 403.
 */
class AdminCartRoutesTest extends AdminCartTestCase
{
    // ─── Admin autenticado puede acceder ─────────────────────────────────────

    #[Test]
    public function admin_puede_abrir_lista_de_modulos(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'web')
            ->get('/admin/cart/modulos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Cart/ModulosIndex'));
    }

    #[Test]
    public function admin_puede_abrir_detalle_de_modulo(): void
    {
        $admin = $this->createAdminUser();

        $solicitud = $this->crearSolicitudPendiente();
        $modulo = ModuloCliente::create([
            'solicitud_id'           => $solicitud->id,
            'nombre'                 => 'Módulo Test',
            'slug'                   => 'modulo-test-' . uniqid(),
            'tipo_modulo'            => 'biblioteca',
            'categorias_autorizadas' => ['prestamo'],
            'activo'                 => true,
        ]);

        $this->actingAs($admin, 'web')
            ->get("/admin/cart/modulos/{$modulo->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Cart/ModuloDetalle')
                ->has('modulo')
                ->where('modulo.id', $modulo->id)
            );
    }

    #[Test]
    public function admin_puede_abrir_lista_de_solicitudes(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'web')
            ->get('/admin/cart/solicitudes')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Cart/SolicitudesIndex'));
    }

    #[Test]
    public function admin_puede_abrir_bitacora(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'web')
            ->get('/admin/cart/bitacora')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Cart/BitacoraIndex'));
    }

    // ─── Usuario no autenticado → redirección ─────────────────────────────────

    #[Test]
    public function usuario_no_autenticado_no_puede_acceder_a_modulos(): void
    {
        $this->get('/admin/cart/modulos')
            ->assertRedirect();
    }

    #[Test]
    public function usuario_no_autenticado_no_puede_acceder_a_solicitudes(): void
    {
        $this->get('/admin/cart/solicitudes')
            ->assertRedirect();
    }

    #[Test]
    public function usuario_no_autenticado_no_puede_acceder_a_bitacora(): void
    {
        $this->get('/admin/cart/bitacora')
            ->assertRedirect();
    }

    // ─── Usuario sin rol admin_carrito → 403 ─────────────────────────────────

    #[Test]
    public function usuario_sin_rol_cart_admin_recibe_403(): void
    {
        $usuario = $this->createRegularUser();

        $this->actingAs($usuario, 'web')
            ->get('/admin/cart/modulos')
            ->assertForbidden();
    }
}
