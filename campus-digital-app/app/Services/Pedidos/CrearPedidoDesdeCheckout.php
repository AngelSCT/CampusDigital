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
 *  4. Valida y cobra saldo del usuario contra M4.8 (SaldoMonedero real)
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

        // 2) Validar saldo contra M4.8 (SaldoMonedero real)
        $this->validarSaldo($dto->usuarioId, $totalPedido, true, null, $dto->modulo);

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
            $producto = $this->consultarProducto($item['producto_id']);
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
    /**
     * Consulta real al catálogo del M4.3.
     * Trae nombre, IVA aplicable y precio vigente del producto.
     *
     * Reemplaza el mock anterior por consultas reales a:
     *   - Catalogo: nombre, descripcion, aplica_iva
     *   - Precio: precio vigente según fecha_inicio/fecha_fin
     */
    private function consultarProducto(int $productoId): array
    {
        // 1. Buscar el producto en el catálogo
        $producto = \App\Models\Catalogo::find($productoId);
        
        if (!$producto) {
            throw ProductoNoDisponibleException::noEncontrado($productoId);
        }
        
        if (!$producto->activo) {
            throw ProductoNoDisponibleException::inactivo($productoId);
        }
        
        // 2. Buscar el precio vigente del producto
        // (fecha_inicio <= hoy <= fecha_fin, o fecha_fin NULL = vigente indefinidamente)
        $hoy = now()->toDateString();
        $precioVigente = \App\Models\Precio::where('id_catalogo', $productoId)
            ->where('fecha_inicio', '<=', $hoy)
            ->where(function ($query) use ($hoy) {
                $query->whereNull('fecha_fin')
                      ->orWhere('fecha_fin', '>=', $hoy);
            })
            ->orderByDesc('fecha_inicio')
            ->first();
        
        if (!$precioVigente) {
            throw ProductoNoDisponibleException::noEncontrado($productoId);
        }
        
        return [
            'nombre'     => $producto->nombre,
            'precio'     => (float) $precioVigente->precio,
            'aplica_iva' => (bool) $producto->aplica_iva,
        ];
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
     * Validar y cargar saldo del usuario contra el M4.8 (Saldo Digital).
     *
     * Usa el modelo SaldoMonedero que ya existe en el proyecto:
     *   - obtenerOCrear(): crea monedero si el usuario no tiene uno
     *   - tieneSaldo(): valida que tenga saldo suficiente
     *   - cargar(): descuenta el monto con transacción atómica
     *
     * Si cobrarSaldo es false (ej: el M4.4 ya cobró), solo valida sin descontar.
     */
    private function validarSaldo(int $usuarioId, float $totalPedido, bool $cobrarSaldo = true, ?string $folio = null, ?string $modulo = null): void
    {
        $monedero = \App\Models\SaldoMonedero::obtenerOCrear($usuarioId);

        if (!$monedero->tieneSaldo($totalPedido)) {
            throw new SaldoInsuficienteException(
                (float) $monedero->saldo_disponible,
                $totalPedido
            );
        }

        // Solo descontar si se indica (cuando el M4.4 ya cobró, no se vuelve a cobrar)
        if ($cobrarSaldo) {
            $monedero->cargar(
                $totalPedido,
                "Pago de pedido " . ($folio ?? 'nuevo'),
                $modulo ?? 'otro'
            );
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