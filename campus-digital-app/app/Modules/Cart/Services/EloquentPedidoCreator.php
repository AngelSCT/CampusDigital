<?php

namespace App\Modules\Cart\Services;

use App\DTOs\CheckoutPedidoDTO;
use App\Models\Cart\Carrito;
use App\Models\Cart\ItemCarrito;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;
use App\Services\Pedidos\CancelarPedidoDesdeCheckout;
use App\Services\Pedidos\CrearPedidoDesdeCheckout;
use Illuminate\Support\Facades\Log;

/**
 * Implementación concreta de PedidoCreatorInterface.
 *
 * Estrategia primaria: llama directamente al Service CrearPedidoDesdeCheckout
 * del Módulo 4.5 (Pedidos y Seguimiento) usando el DTO CheckoutPedidoDTO.
 *
 * Fallback: si M4.5 no está disponible (no-2xx), crea el pedido localmente
 * en la tabla `pedido` de la misma BD, preservando la atomicidad transaccional.
 *
 * Idempotencia: verifica `carrito_uuid` único en `pedido` antes de cualquier escritura.
 *
 * `usuario_id` se resuelve desde `carrito->usuario_ref` (numérico directo o matrícula).
 */
final class EloquentPedidoCreator implements PedidoCreatorInterface
{
    private const MODULO_MAP = [
        'biblioteca' => 'biblioteca',
        'cafeteria'  => 'cafeteria',
        'copias'     => 'copias',
        'souvenirs'  => 'souvenirs',
        'catalogo'   => 'otro',
    ];

    public function crearDesdeCarrito(Carrito $carrito): void
    {
        // Idempotencia: si ya existe un Pedido para este carrito, no crear otro
        if (Pedido::where('carrito_uuid', (string) $carrito->uuid)->exists()) {
            return;
        }

        $items = ItemCarrito::where('carrito_id', $carrito->id)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->with('categoria')
            ->get();

        $moduloPedido = self::MODULO_MAP[$carrito->modulo?->tipo_modulo ?? ''] ?? 'otro';

        // Folio determinista: PED-{primeros 20 hex chars del carrito uuid sin guiones}
        // Siempre el mismo para el mismo carrito → seguro ante reintentos.
        $hexUuid = str_replace('-', '', (string) $carrito->uuid);
        $folio   = 'PED-' . strtoupper(substr($hexUuid, 0, 20));

        $descripcion = $items->map(fn($i) => "{$i->nombre} x{$i->cantidad}")->implode(', ');

        // Resolver usuario_id desde usuario_ref.
        // usuario_ref puede ser: un ID numérico (strval de auth()->id()) o una matrícula string.
        $usuarioId = null;
        $usuarioRef = $carrito->usuario_ref;

        if (is_numeric($usuarioRef)) {
            $usuarioId = (int) $usuarioRef;
        } else {
            $usuario = Usuario::where('id', $usuarioRef)
                ->orWhere('matricula', $usuarioRef)
                ->first();
            $usuarioId = $usuario?->id;
        }

        if ($usuarioId === null) {
            throw new \RuntimeException(
                "EloquentPedidoCreator: no se pudo resolver usuario_id desde usuario_ref='{$usuarioRef}'. "
                . "Verifica que el carrito almacene un ID numérico o matrícula válida."
            );
        }

        // ── Preparar payload para M4.5 ──────────────────────────────────────
        $moduloMapCat = [
            'cafeteria' => 'cafeteria',
            'copias'    => 'copias',
            'souvenirs' => 'souvenirs',
            'tramites'  => 'otro',
            'servicios' => 'otro',
        ];
        $moduloPorCategoria = $moduloMapCat[$items->first()?->categoria?->slug ?? ''] ?? $moduloPedido;

        $itemsPayload = $items->map(function ($item) {
            $idCatalogo = $item->metadata['id_catalogo']
                ?? (int) str_replace('CAT-', '', $item->referencia_externa);
            return [
                'producto_id' => $idCatalogo,
                'cantidad'    => $item->cantidad,
            ];
        })->toArray();

        // ── Llamar al Service de M4.5 (estrategia primaria) ─────────────────
        $enviado = false;
        try {
            app(CrearPedidoDesdeCheckout::class)->ejecutar(
                new CheckoutPedidoDTO(
                    usuarioId:   $usuarioId,
                    modulo:      $moduloPorCategoria,
                    items:       $itemsPayload,
                    descripcion: $descripcion ?: 'Pedido desde carrito',
                    metaJson:    [
                        'carrito_uuid' => (string) $carrito->uuid,
                        'usuario_ref'  => $carrito->usuario_ref,
                        'modulo_slug'  => $carrito->modulo?->slug,
                    ],
                    carritoUuid: (string) $carrito->uuid,
                    cobrarSaldo: false, // M4.4 gestiona el saldo por separado vía SaldoClient
                )
            );
            $enviado = true;
            // M4.5 creó el pedido con folio, ítems, historial y bitácora.
            return;
        } catch (\Throwable $e) {
            Log::warning('[EloquentPedidoCreator] Servicio M4.5 falló; usando fallback local', [
                'error'        => $e->getMessage(),
                'carrito_uuid' => (string) $carrito->uuid,
            ]);
        }

        // ── Fallback: escritura local si M4.5 no respondió exitosamente ─────
        if (!$enviado) {
            Pedido::create([
                'usuario_id'       => $usuarioId,
                'numero_folio'     => $folio,
                'estado'           => 'creado',
                'modulo'           => $moduloPedido,
                'total'            => $carrito->total,
                'descripcion'      => $descripcion ?: 'Pedido desde carrito',
                'cobrado_de_saldo' => $carrito->requiere_saldo,
                'carrito_uuid'     => (string) $carrito->uuid,
                'meta_json'        => [
                    'carrito_uuid' => (string) $carrito->uuid,
                    'usuario_ref'  => $carrito->usuario_ref,
                    'modulo_slug'  => $carrito->modulo?->slug,
                    'items'        => $items->map(fn($i) => [
                        'referencia_externa' => $i->referencia_externa,
                        'nombre'             => $i->nombre,
                        'precio_unitario'    => (float) $i->precio_unitario,
                        'cantidad'           => $i->cantidad,
                        'categoria_slug'     => $i->categoria?->slug,
                    ])->toArray(),
                ],
            ]);
        }
    }

    public function cancelarPedidoDeCarrito(string $carritoUuid): void
    {
        // Notificar al Service de M4.5 (best-effort — ignora el resultado)
        try {
            app(CancelarPedidoDesdeCheckout::class)
                ->ejecutar($carritoUuid, 'Conciliación fallida desde carrito');
        } catch (\Throwable) {
            // silencioso — el fallback local es la fuente de verdad
        }

        // Fallback local: cancelar en tabla `pedido` si existe
        $pedido = Pedido::where('carrito_uuid', $carritoUuid)->first();

        if ($pedido && $pedido->estado !== 'cancelado') {
            $pedido->update(['estado' => 'cancelado']);
        }
    }
}
