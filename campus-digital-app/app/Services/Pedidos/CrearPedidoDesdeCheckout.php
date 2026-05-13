<?php

namespace App\Services\Pedidos;

use App\DTOs\CheckoutPedidoDTO;
use App\Exceptions\Pedidos\ProductoNoDisponibleException;
use App\Exceptions\Pedidos\SaldoInsuficienteException;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\PedidoHistorial;
use App\Models\ActividadBitacora;
use App\Support\MaquinaEstadosPedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Service: crear un Pedido en el M4.5 desde un checkout externo (M4.4 u otro).
 *
 * Flujo:
 *  1. Recibe un DTO validado con usuario, módulo e items {producto_id, cantidad}
 *  2. Resuelve cada item contra el catálogo (M4.3) — hoy mock, mañana real
 *  3. Calcula total con IVA cuando aplique
 *  4. Valida saldo del usuario contra M4.2 (hoy mock, mañana real)
 *  5. Crea Pedido + PedidoItems + Historial + Bitácora en UNA SOLA transacción
 *  6. Devuelve el Pedido completo con sus items cargados
 *
 * Lanza:
 *  - ProductoNoDisponibleException si un producto del catálogo no existe
 *  - SaldoInsuficienteException si el usuario no tiene saldo suficiente
 *  - InvalidArgumentException (del DTO) si los datos son inválidos
 */
class CrearPedidoDesdeCheckout
{
    /**
     * Punto de entrada principal.
     */
    public function ejecutar(CheckoutPedidoDTO $dto): Pedido
    {
        // 1) Resolver items contra el catálogo y calcular totales
        $itemsResueltos = $this->resolverItems($dto->items);
        $totalPedido = $this->calcularTotal($itemsResueltos);

        // 2) Validar saldo (mock por ahora)
        $this->validarSaldo($dto->usuarioId, $totalPedido);

        // 3) Crear todo en una sola transacción
        return DB::transaction(function () use ($dto, $itemsResueltos, $totalPedido) {
            // 3.1) Crear el pedido (Pedido::generarFolio ya hace lockForUpdate)
            $pedido = Pedido::create([
                'usuario_id'  => $dto->usuarioId,
                'numero_folio' => Pedido::generarFolio(),
                'estado'      => 'creado',
                'modulo'      => $dto->modulo,
                'total'       => $totalPedido,
                'descripcion' => $dto->descripcion ?? "Pedido desde checkout",
                'notas'       => '',
                'meta_json'   => $dto->metaJson ?? ['origen' => 'checkout_service'],
            ]);

            // 3.2) Crear los items con snapshot histórico
            foreach ($itemsResueltos as $item) {
                PedidoItem::create([
                    'pedido_id'       => $pedido->id,
                    'producto_id'     => $item['producto_id'],
                    'nombre_producto' => $item['nombre_producto'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'aplica_iva'      => $item['aplica_iva'],
                    'subtotal'        => $item['subtotal'],
                    'iva_monto'       => $item['iva_monto'],
                    'total_linea'     => $item['total_linea'],
                ]);
            }

            // 3.3) Registrar evento inicial en historial
            PedidoHistorial::create([
                'pedido_id'       => $pedido->id,
                'estado_anterior' => null,
                'estado_nuevo'    => 'creado',
                'usuario_id'      => $dto->usuarioId,
                'notas'           => 'Pedido creado desde checkout',
            ]);

            // 3.4) Registrar en bitácora general (si está disponible)
            $this->registrarBitacora($pedido, $dto);

            return $pedido->load('items', 'historial');
        });
    }

    // ────────────────────────────────────────────────────────────
    // RESOLUCIÓN DE PRODUCTOS (mock — se reemplaza cuando el M4.3 esté listo)
    // ────────────────────────────────────────────────────────────

    /**
     * Por cada item del DTO, traer del catálogo: nombre, precio, IVA.
     * Calcula subtotal, IVA y total por línea.
     *
     * 🔧 TODO: cuando el M4.3 suba migraciones, reemplazar el mock por:
     *      Catalogo::find($productoId) + Precio::where(...)->first()
     */
    private function resolverItems(array $items): array
    {
        $resueltos = [];

        foreach ($items as $item) {
            $producto = $this->consultarProductoMock($item['producto_id']);
            $precioUnitario = $producto['precio'];
            $cantidad = $item['cantidad'];
            $aplicaIva = $producto['aplica_iva'];

            $subtotal = $cantidad * $precioUnitario;
            $ivaMonto = $aplicaIva ? round($subtotal * 0.16, 2) : 0;
            $totalLinea = $subtotal + $ivaMonto;

            $resueltos[] = [
                'producto_id'     => $item['producto_id'],
                'nombre_producto' => $producto['nombre'],
                'cantidad'        => $cantidad,
                'precio_unitario' => $precioUnitario,
                'aplica_iva'      => $aplicaIva,
                'subtotal'        => $subtotal,
                'iva_monto'       => $ivaMonto,
                'total_linea'     => $totalLinea,
            ];
        }

        return $resueltos;
    }

    /**
     * MOCK temporal del catálogo del M4.3.
     * Sustituir por consultas reales cuando ellos suban migraciones.
     */
    private function consultarProductoMock(int $productoId): array
    {
        // Productos hardcoded de prueba
        $catalogo = [
            1 => ['nombre' => 'Café americano',     'precio' => 35.00, 'aplica_iva' => false],
            2 => ['nombre' => 'Café con leche',     'precio' => 40.00, 'aplica_iva' => false],
            3 => ['nombre' => 'Dona glaseada',      'precio' => 20.00, 'aplica_iva' => false],
            4 => ['nombre' => 'Sandwich de jamón',  'precio' => 55.00, 'aplica_iva' => false],
            5 => ['nombre' => 'Agua mineral',       'precio' => 15.00, 'aplica_iva' => false],
            10 => ['nombre' => 'Copia B/N',          'precio' => 1.00,  'aplica_iva' => true],
            11 => ['nombre' => 'Copia a color',      'precio' => 3.00,  'aplica_iva' => true],
            12 => ['nombre' => 'Impresión doble carta', 'precio' => 5.00, 'aplica_iva' => true],
            20 => ['nombre' => 'Playera Campus',     'precio' => 250.00,'aplica_iva' => true],
            21 => ['nombre' => 'Termo institucional','precio' => 180.00,'aplica_iva' => true],
        ];

        if (!isset($catalogo[$productoId])) {
            throw ProductoNoDisponibleException::noEncontrado($productoId);
        }

        return $catalogo[$productoId];
    }

    // ────────────────────────────────────────────────────────────
    // CÁLCULOS Y VALIDACIONES
    // ────────────────────────────────────────────────────────────

    private function calcularTotal(array $itemsResueltos): float
    {
        return round(
            collect($itemsResueltos)->sum('total_linea'),
            2
        );
    }

    /**
     * Validar saldo del usuario contra el M4.2.
     *
     * 🔧 TODO: cuando el M4.2 esté listo, reemplazar el mock por:
     *      Http::get("M4.2/saldo/{$usuarioId}") o servicio compartido
     *
     * Por ahora siempre aprueba (mock).
     */
    private function validarSaldo(int $usuarioId, float $totalPedido): void
    {
        // MOCK: siempre tiene saldo suficiente
        $saldoActual = 9999.99;

        if ($saldoActual < $totalPedido) {
            throw new SaldoInsuficienteException($saldoActual, $totalPedido);
        }
    }

    // ────────────────────────────────────────────────────────────
    // BITÁCORA
    // ────────────────────────────────────────────────────────────

    private function registrarBitacora(Pedido $pedido, CheckoutPedidoDTO $dto): void
    {
        try {
            ActividadBitacora::create([
                'usuario_id'  => $dto->usuarioId,
                'accion'      => 'pedido.creado_desde_checkout',
                'descripcion' => "Pedido {$pedido->numero_folio} creado desde checkout, total: \${$pedido->total}",
                'ip'          => Request::ip() ?? '127.0.0.1',
                'meta_json'   => [
                    'pedido_id'   => $pedido->id,
                    'modulo'      => $pedido->modulo,
                    'items_count' => count($dto->items),
                ],
            ]);
        } catch (\Throwable $e) {
            // Si la bitácora falla, no rompe la creación del pedido
            \Log::warning('No se pudo registrar en bitácora', [
                'pedido_id' => $pedido->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}