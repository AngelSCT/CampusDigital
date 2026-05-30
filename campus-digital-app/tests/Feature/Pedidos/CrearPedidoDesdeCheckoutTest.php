<?php

namespace Tests\Feature\Pedidos;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\DTOs\CheckoutPedidoDTO;
use App\Services\Pedidos\CrearPedidoDesdeCheckout;
use App\Services\Pedidos\CancelarPedidoDesdeCheckout;
use App\Exceptions\Pedidos\ProductoNoDisponibleException;
use App\Exceptions\Pedidos\SaldoInsuficienteException;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\SaldoMonedero;

class CrearPedidoDesdeCheckoutTest extends TestCase
{
    /**
     * Test 1: Crear pedido exitosamente con productos reales del catálogo.
     */
    public function test_crear_pedido_exitoso_con_catalogo_real(): void
    {
        // Asegurar que el usuario tenga saldo
        $monedero = SaldoMonedero::obtenerOCrear(1);
        if (!$monedero->tieneSaldo(200)) {
            $monedero->abonar(1000, 'Recarga para test', 'recargas');
        }

        $dto = new CheckoutPedidoDTO(
            usuarioId: 1,
            modulo: 'cafeteria',
            items: [
                ['producto_id' => 1, 'cantidad' => 2],  // Café Americano
                ['producto_id' => 2, 'cantidad' => 1],  // Sándwich
            ],
            descripcion: 'Test PHPUnit - pedido exitoso'
        );

        $service = app(CrearPedidoDesdeCheckout::class);
        $pedido = $service->ejecutar($dto);

        // Verificaciones
        $this->assertNotNull($pedido->id);
        $this->assertEquals('creado', $pedido->estado);
        $this->assertEquals('cafeteria', $pedido->modulo);
        $this->assertStringStartsWith('PED-', $pedido->numero_folio);
        $this->assertEquals(2, $pedido->items->count());
        $this->assertGreaterThan(0, (float) $pedido->total);
    }

    /**
     * Test 2: Producto inexistente lanza excepción.
     */
    public function test_producto_inexistente_lanza_excepcion(): void
    {
        $this->expectException(ProductoNoDisponibleException::class);

        $dto = new CheckoutPedidoDTO(
            usuarioId: 1,
            modulo: 'cafeteria',
            items: [['producto_id' => 9999, 'cantidad' => 1]],
        );

        app(CrearPedidoDesdeCheckout::class)->ejecutar($dto);
    }

    /**
     * Test 3: Saldo insuficiente lanza excepción.
     */
    public function test_saldo_insuficiente_lanza_excepcion(): void
    {
        // Crear usuario con saldo bajo
        $monedero = SaldoMonedero::obtenerOCrear(3); // estudiante
        // Asegurarse que tenga poco saldo
        if ($monedero->saldo_disponible > 0) {
            try { $monedero->cargar((float)$monedero->saldo_disponible, 'Vaciar para test', 'otro'); } catch (\Exception $e) {}
        }

        $this->expectException(SaldoInsuficienteException::class);

        $dto = new CheckoutPedidoDTO(
            usuarioId: 3,
            modulo: 'souvenirs',
            items: [['producto_id' => 11, 'cantidad' => 100]], // 100 camisetas = imposible
        );

        app(CrearPedidoDesdeCheckout::class)->ejecutar($dto);
    }

    /**
     * Test 4: Idempotencia por carrito_uuid.
     */
    public function test_idempotencia_por_carrito_uuid(): void
    {
        $monedero = SaldoMonedero::obtenerOCrear(1);
        if (!$monedero->tieneSaldo(200)) {
            $monedero->abonar(1000, 'Recarga para test idempotencia', 'recargas');
        }

        $uuid = 'test-idempotencia-' . uniqid();

        $dto = new CheckoutPedidoDTO(
            usuarioId: 1,
            modulo: 'cafeteria',
            items: [['producto_id' => 1, 'cantidad' => 1]],
            carritoUuid: $uuid,
        );

        $service = app(CrearPedidoDesdeCheckout::class);
        $pedido1 = $service->ejecutar($dto);
        $pedido2 = $service->ejecutar($dto); // Mismo UUID

        $this->assertEquals($pedido1->id, $pedido2->id, 'Debe retornar el mismo pedido');
    }

    /**
     * Test 5: Cancelar pedido reembolsa saldo.
     */
    public function test_cancelar_pedido_reembolsa_saldo(): void
    {
        $monedero = SaldoMonedero::obtenerOCrear(1);
        if (!$monedero->tieneSaldo(200)) {
            $monedero->abonar(1000, 'Recarga para test cancelar', 'recargas');
        }

        $saldoAntes = (float) SaldoMonedero::where('usuario_id', 1)->first()->saldo_disponible;

        $uuid = 'test-cancelar-' . uniqid();

        // Crear pedido
        $dto = new CheckoutPedidoDTO(
            usuarioId: 1,
            modulo: 'cafeteria',
            items: [['producto_id' => 1, 'cantidad' => 1]], // $18
            carritoUuid: $uuid,
        );
        $pedido = app(CrearPedidoDesdeCheckout::class)->ejecutar($dto);
        $totalCobrado = (float) $pedido->total;

        // Cancelar
        $cancelado = app(CancelarPedidoDesdeCheckout::class)->ejecutar($uuid, 'Test cancelación');

        $this->assertEquals('cancelado', $cancelado->estado);

        // Verificar reembolso
        $saldoDespues = (float) SaldoMonedero::where('usuario_id', 1)->first()->saldo_disponible;
        $this->assertEquals($saldoAntes, $saldoDespues, 'Saldo debe volver al valor original tras cancelación');
    }

    /**
     * Test 6: DTO rechaza módulo inválido.
     */
    public function test_dto_rechaza_modulo_invalido(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CheckoutPedidoDTO(
            usuarioId: 1,
            modulo: 'modulo_inventado',
            items: [['producto_id' => 1, 'cantidad' => 1]],
        );
    }

    /**
     * Test 7: DTO rechaza items vacíos.
     */
    public function test_dto_rechaza_items_vacios(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CheckoutPedidoDTO(
            usuarioId: 1,
            modulo: 'cafeteria',
            items: [],
        );
    }

    /**
     * Test 8: Endpoint HTTP checkout crea pedido.
     */
    public function test_endpoint_checkout_crea_pedido(): void
    {
        $monedero = SaldoMonedero::obtenerOCrear(1);
        if (!$monedero->tieneSaldo(200)) {
            $monedero->abonar(1000, 'Recarga para test HTTP', 'recargas');
        }

        $response = $this->postJson('/api/pedidos/checkout', [
            'usuario_id' => 1,
            'modulo' => 'cafeteria',
            'items' => [
                ['producto_id' => 1, 'cantidad' => 1],
            ],
            'carrito_uuid' => 'http-phpunit-' . uniqid(),
        ], [
            'X-API-KEY' => 'dev-local-key-campus-digital-2026',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'pedido' => ['id', 'numero_folio', 'estado', 'total'],
                 ]);
    }
}