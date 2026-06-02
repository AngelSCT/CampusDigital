<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Carrito;
use App\Models\Cart\ItemCarrito;
use App\Models\Pedido;
use App\Models\Usuario;
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
 * `usuario_id` se resuelve desde `carrito->usuario_ref`:
 *   - Si es numérico → se usa directamente como FK integer.
 *   - Si es matrícula string → se busca en `usuario.matricula`.
 *   - Si no se puede resolver → se lanza RuntimeException (evita insertar null
 *     en columnas NOT NULL y producir errores crípticos de BD).
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

    public function cancelarPedidoDeCarrito(string $carritoUuid): void
    {
        $pedido = Pedido::where('carrito_uuid', $carritoUuid)->first();

        if ($pedido && $pedido->estado !== 'cancelado') {
            $pedido->update(['estado' => 'cancelado']);
        }
    }
}
