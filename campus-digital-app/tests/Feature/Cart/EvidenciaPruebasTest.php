<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Carrito;
use App\Modules\Cart\Services\ModuleTokenService;
use PHPUnit\Framework\Attributes\Test;

/**
 * Casos de evidencia que no están cubiertos en otros suites.
 *
 * Casos cubiertos aquí:
 *   TC-04 — Error 422: checkout con carrito vacío (validación backend).
 *
 * Casos cubiertos en suites existentes (referenciados en docs/evidencia-pruebas.md):
 *   TC-01 — TokenTest::token_valido_pasa_middleware
 *   TC-02 — TokenTest::token_expirado_devuelve_401_token_expired
 *             + TokenTest::refresh_emite_par_nuevo_y_revoca_el_viejo
 *   TC-03 — SaldoIntegrationTest::checkout_con_saldo_confirmado_pasa_a_estado_confirmado
 *   TC-05 — TokenTest::categoria_no_autorizada_devuelve_403_scope_denied
 *   TC-06 — SaldoIntegrationTest::checkout_con_saldo_caido_y_categoria_sin_diferido_devuelve_503
 */
class EvidenciaPruebasTest extends CartTestCase
{
    private \App\Models\Cart\ModuloCliente $modulo;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();

        $this->modulo = $this->createTestModuloCliente([
            'slug'                   => 'modulo-evidencia',
            'categorias_autorizadas' => ['prestamo', 'reserva'],
        ]);

        $par         = (new ModuleTokenService())->issuePair($this->modulo);
        $this->token = $par['access_token'];
    }

    // ─── TC-04: Error 422 — Validación backend ────────────────────────────────

    #[Test]
    public function checkout_carrito_vacio_devuelve_422_checkout_error(): void
    {
        // Carrito sin ítems → CartBusinessException → HTTP 422
        $carrito = Carrito::create([
            'uuid'           => 'uuid-ev422-' . uniqid(),
            'modulo_id'      => $this->modulo->id,
            'usuario_ref'    => 'MAT-EV422',
            'estado'         => Carrito::ESTADO_ABIERTO,
            'requiere_saldo' => false,
            'total'          => '0.00',
        ]);

        $this->withToken($this->token)
            ->postJson("/api/cart/carritos/{$carrito->uuid}/checkout")
            ->assertStatus(422)
            ->assertJsonPath('error', 'CHECKOUT_ERROR')
            ->assertJsonPath('mensaje', 'No se puede hacer checkout de un carrito vacío.');
    }
}
