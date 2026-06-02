<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Carrito;
use App\Models\Cart\ItemCarrito;
use App\Models\Pedido;
use App\Modules\Cart\Contracts\PedidoCreatorInterface;

/**
 * Implementación concreta de PedidoCreatorInterface usando Eloquent.
 *
 * Escribe directamente en la tabla `pedido` de la misma BD (campus_digital).
 * Diseñada para ejecutarse DENTRO de la DB::transaction que confirma el Carrito,
 * garantizando atomicidad entre ambas escrituras.
 *
 * Idempotencia: usa `carrito_uuid` (columna unique en `pedido`) para garantizar
 * que una segunda llamada con el mismo carrito retorne sin crear un segundo Pedido.
 *
 * Nota: `usuario_id` se deja en null porque el módulo Carrito identifica usuarios
 * por `usuario_ref` (string/matrícula), no por FK integer. El equipo de Pedidos
 * puede resolver el usuario_id desde usuario_ref usando meta_json['usuario_ref']
 * en un proceso asíncrono posterior si lo requiere.
 */
final class EloquentPedidoCreator implements PedidoCreatorInterface
{
    private const MODULO_MAP = [
        'biblioteca' => 'biblioteca',
        'cafeteria'  => 'cafeteria',
        'copias'     => 'copias',
        'souvenirs'  => 'souvenirs',
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

        Pedido::create([
            'usuario_id'       => null,
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

    public function cancelarPedidoDeCarrito(string $carritoUuid): void
    {
        $pedido = Pedido::where('carrito_uuid', $carritoUuid)->first();

        if ($pedido && $pedido->estado !== 'cancelado') {
            $pedido->update(['estado' => 'cancelado']);
        }
    }
}
