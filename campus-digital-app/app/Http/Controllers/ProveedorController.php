<?php

namespace App\Http\Controllers;

use App\Models\ActividadBitacora;
use App\Models\PedidoTienda;
use App\Services\ExternalModulesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Vista operativa del Proveedor — Módulo 4.9 (Preview)
 *
 * Desacoplamiento total: este controlador SOLO toca la tabla `pedidos`.
 * Saldo, stock y mermas van exclusivamente por ExternalModulesService.
 */
class ProveedorController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /proveedor/pedidos  (web, Inertia)
    // El proveedor ve únicamente los pedidos que contienen productos de su tienda.
    // -------------------------------------------------------------------------
    public function index(): InertiaResponse
    {
        $usuario = Auth::user();
        $tienda  = $usuario->tienda ?? $usuario->nombre;

        // Carga pedidos cuyo detalle incluya al menos un producto de esta tienda.
        // SOLO tocamos la tabla `pedidos` (y `pedido_detalles`/`productos` para filtrar).
        $pedidos = PedidoTienda::with(['detalles' => function ($q) use ($tienda) {
                $q->with('producto')
                  ->whereHas('producto', fn($q2) => $q2->where('tienda', $tienda));
            }])
            ->whereHas('detalles', fn($q) =>
                $q->whereHas('producto', fn($q2) => $q2->where('tienda', $tienda))
            )
            ->whereIn('estado', ['pagado', 'esperando_pago', 'en_escrow'])
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Proveedor/Pedidos', [
            'pedidos' => $pedidos,
            'tienda'  => $tienda,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /proveedor/pedidos/{pedido}/cancelar  (web, auth)
    // El proveedor cancela un pedido por falta de stock.
    //
    // Body:
    //   productos_danados: int[]  — IDs de productos a registrar como merma (opcional)
    //
    // Lógica:
    //   1. Verificar que el pedido pertenece a esta tienda
    //   2. Reembolsar saldo vía ExternalModulesService (desde cuenta puente si es regalo)
    //   3. Liberar stock vía ExternalModulesService
    //   4. Si hay productos dañados → registrar merma vía ExternalModulesService
    //   5. Actualizar estado en `pedidos` (ÚNICA tabla que tocamos directamente)
    // -------------------------------------------------------------------------
    public function cancelarPedido(Request $request, PedidoTienda $pedido): JsonResponse
    {
        $usuario = Auth::user();
        $tienda  = $usuario->tienda ?? $usuario->nombre;

        $data = $request->validate([
            'productos_danados'   => ['nullable', 'array'],
            'productos_danados.*' => ['integer'],
        ]);

        // ── 1. Verificar que el pedido tiene productos de esta tienda ─────────
        $perteneceATienda = $pedido->detalles()
            ->whereHas('producto', fn($q) => $q->where('tienda', $tienda))
            ->exists();

        if (! $perteneceATienda) {
            return response()->json([
                'mensaje' => 'Este pedido no contiene productos de tu tienda.',
            ], 403);
        }

        if (! in_array($pedido->estado, ['pagado', 'esperando_pago', 'en_escrow'])) {
            return response()->json([
                'mensaje' => "No se puede cancelar un pedido en estado '{$pedido->estado}'.",
            ], 422);
        }

        $modules        = new ExternalModulesService();
        $productosDanados = $data['productos_danados'] ?? [];

        // ── 2. Reembolsar saldo ───────────────────────────────────────────────
        // Si era regalo en escrow, el reembolso libera la cuenta puente al comprador.
        $modules->reembolsarSaldo(
            usuarioId: $pedido->usuario_id,
            monto:     (float) $pedido->total,
            concepto:  "Cancelación por proveedor '{$tienda}' — pedido #{$pedido->id}"
        );

        // ── 3. Liberar stock + registrar merma si aplica ─────────────────────
        $detalles = $pedido->detalles()->with('producto')->get();

        foreach ($detalles as $detalle) {
            // Solo procesamos detalles de esta tienda
            if (($detalle->producto->tienda ?? '') !== $tienda) {
                continue;
            }

            // Liberar stock (vía servicio externo — no tocamos tabla productos)
            $modules->liberarStock($detalle->producto_id, $detalle->cantidad);

            // Si el producto fue marcado como dañado → registrar merma
            if (in_array($detalle->producto_id, $productosDanados)) {
                $modules->registrarMerma(
                    productoId: $detalle->producto_id,
                    cantidad:   $detalle->cantidad,
                    motivo:     "Cancelación por proveedor — producto dañado"
                );
            }
        }

        // ── 4. Actualizar estado del pedido (ÚNICO toque a BD propia) ─────────
        $pedido->update(['estado' => 'cancelado']);

        // ── 5. Bitácora ───────────────────────────────────────────────────────
        ActividadBitacora::create([
            'usuario_id'   => $usuario->id,
            'accion'       => 'proveedor_cancelacion',
            'modulo'       => 'tienda',
            'target_tabla' => 'pedidos',
            'target_id'    => $pedido->id,
            'exito'        => true,
            'detalle'      => "Proveedor '{$tienda}' canceló pedido #{$pedido->id} por falta de stock.",
            'ip'           => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'meta_json'    => [
                'tienda'            => $tienda,
                'productos_danados' => $productosDanados,
                'total_reembolsado' => $pedido->total,
            ],
        ]);

        return response()->json([
            'mensaje'    => "Pedido #{$pedido->id} cancelado. Reembolso procesado.",
            'reembolsado' => (float) $pedido->total,
        ]);
    }
}
