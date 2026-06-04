<?php

namespace Tests\Feature\Cart;

use App\Models\Cart\Carrito;
use App\Models\Cart\Categoria;
use App\Models\Cart\ItemCarrito;
use App\Modules\Cart\Services\CarritoService;
use App\Modules\Cart\Services\ModuleTokenService;
use PHPUnit\Framework\Attributes\Test;

class ComprobanteEndpointTest extends CartTestCase
{
    private \App\Models\Cart\ModuloCliente $modulo;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpJwtConfig();
        $this->seedCategorias();

        $this->modulo = $this->createTestModuloCliente([
            'slug'                   => 'test-comp-' . uniqid(),
            'tipo_modulo'            => 'catalogo',
            'categorias_autorizadas' => ['prestamo'],
        ]);

        $par         = (new ModuleTokenService())->issuePair($this->modulo);
        $this->token = $par['access_token'];
    }

    // ─── TC1: carrito confirmado → 200 con estructura completa ────────────────

    #[Test]
    public function carrito_confirmado_devuelve_200_con_comprobante(): void
    {
        $carritoService = new CarritoService();
        $carrito = $carritoService->crear($this->modulo, [
            'usuario_ref'    => '1',
            'requiere_saldo' => true,
        ]);

        // Añadir un ítem
        $cat = Categoria::where('slug', 'prestamo')->first();
        ItemCarrito::create([
            'carrito_id'         => $carrito->id,
            'categoria_id'       => $cat->id,
            'referencia_externa' => 'LIBRO-001',
            'nombre'             => 'Cálculo Stewart',
            'precio_unitario'    => 0.00,
            'cantidad'           => 1,
            'added_at'           => now(),
        ]);

        // Simular confirmación directa
        $carrito->update([
            'estado'       => Carrito::ESTADO_CONFIRMADO,
            'confirmed_at' => now(),
            'total'        => '0.00',
        ]);

        $this->withToken($this->token)
            ->getJson("/api/cart/comprobantes/{$carrito->uuid}")
            ->assertOk()
            ->assertJsonStructure([
                'carrito_uuid',
                'fecha_confirmacion',
                'usuario_ref',
                'modulo',
                'items',
                'total',
                'requiere_saldo',
                'estado',
            ])
            ->assertJsonPath('carrito_uuid', (string) $carrito->uuid)
            ->assertJsonPath('usuario_ref', '1')
            ->assertJsonPath('estado', Carrito::ESTADO_CONFIRMADO)
            ->assertJsonPath('requiere_saldo', true)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.referencia_externa', 'LIBRO-001');
    }

    // ─── TC2: carrito abierto (no confirmado) → 404 ────────────────────────

    #[Test]
    public function carrito_abierto_devuelve_404(): void
    {
        $carritoService = new CarritoService();
        $carrito = $carritoService->crear($this->modulo, [
            'usuario_ref'    => '1',
            'requiere_saldo' => false,
        ]);

        // Estado = 'abierto' — no confirmado

        $this->withToken($this->token)
            ->getJson("/api/cart/comprobantes/{$carrito->uuid}")
            ->assertNotFound()
            ->assertJsonPath('error', 'COMPROBANTE_NO_ENCONTRADO');
    }

    // ─── TC3: uuid inexistente → 404 ──────────────────────────────────────────

    #[Test]
    public function uuid_inexistente_devuelve_404(): void
    {
        $this->withToken($this->token)
            ->getJson('/api/cart/comprobantes/00000000-0000-0000-0000-000000000000')
            ->assertNotFound()
            ->assertJsonPath('error', 'COMPROBANTE_NO_ENCONTRADO');
    }
}
