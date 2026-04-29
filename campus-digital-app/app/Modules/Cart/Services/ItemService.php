<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\Categoria;
use App\Models\Cart\ItemCarrito;
use App\Models\Cart\ModuloCliente;
use App\Modules\Cart\Exceptions\CartBusinessException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Exceptions\ScopeDeniedException;

class ItemService
{
    public function __construct(private readonly CarritoService $carritoService) {}

    /**
     * Agrega un ítem al carrito o incrementa la cantidad si ya existe (Cambio 3).
     *
     * @return array{accion: string, item_id: int, cantidad_actual: int, total_actualizado: string}
     * @throws ScopeDeniedException
     * @throws CartStateException
     * @throws CartBusinessException
     */
    public function agregar(Carrito $carrito, ModuloCliente $modulo, array $jwtPayload, array $data): array
    {
        $this->carritoService->assertOperable($carrito);

        // Paso 9 del middleware: verificar scope de categoría (sección 7.4).
        $slug = $data['categoria_slug'];
        if (!in_array($slug, $jwtPayload['categorias_autorizadas'] ?? [], true)) {
            throw new ScopeDeniedException();
        }

        $categoria = Categoria::where('slug', $slug)->first();
        if (!$categoria || !$categoria->activa) {
            throw new CartBusinessException("Categoría '{$slug}' no disponible.");
        }

        $cantidad   = (int) $data['cantidad'];
        $cantMax    = $this->getRegla($categoria, 'cantidad_maxima', PHP_INT_MAX);
        $durMax     = $this->getRegla($categoria, 'duracion_maxima_horas', null);

        // Validar duración si aplica.
        if ($durMax !== null && isset($data['duracion_horas']) && $data['duracion_horas'] > $durMax) {
            throw new CartBusinessException(
                "La duración máxima para '{$slug}' es {$durMax} horas."
            );
        }

        // Cambio 3: ítem duplicado por referencia_externa → incrementar cantidad.
        $existing = ItemCarrito::where('carrito_id', $carrito->id)
            ->where('referencia_externa', $data['referencia_externa'])
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->first();

        if ($existing) {
            $nuevaCantidad = $existing->cantidad + $cantidad;
            if ($nuevaCantidad > $cantMax) {
                throw new CartBusinessException(
                    "La cantidad resultante ({$nuevaCantidad}) supera el máximo permitido ({$cantMax})."
                );
            }
            $existing->update(['cantidad' => $nuevaCantidad]);
            $this->recalcularTotal($carrito);
            $accion = 'incrementado';
            $item   = $existing->fresh();
        } else {
            if ($cantidad > $cantMax) {
                throw new CartBusinessException(
                    "La cantidad ({$cantidad}) supera el máximo permitido ({$cantMax}) para '{$slug}'."
                );
            }
            $item = ItemCarrito::create([
                'carrito_id'         => $carrito->id,
                'categoria_id'       => $categoria->id,
                'referencia_externa' => $data['referencia_externa'],
                'nombre'             => $data['nombre'],
                'precio_unitario'    => $data['precio_unitario'],
                'cantidad'           => $cantidad,
                'duracion_horas'     => $data['duracion_horas'] ?? null,
                'metadata'           => $data['metadata'] ?? null,
                'added_at'           => now(),
            ]);
            $this->recalcularTotal($carrito);
            $accion = 'creado';
        }

        Bitacora::create([
            'accion'       => Bitacora::ACCION_ITEM_AGREGADO,
            'modulo_id'    => $modulo->id,
            'carrito_uuid' => $carrito->uuid,
            'payload'      => ['referencia_externa' => $data['referencia_externa'], 'accion' => $accion],
        ]);

        $carrito->refresh();

        return [
            'accion'           => $accion,
            'item_id'          => $item->id,
            'carrito_uuid'     => $carrito->uuid,
            'cantidad_actual'  => $item->fresh()->cantidad,
            'total_actualizado' => $carrito->total,
        ];
    }

    /** @throws CartStateException */
    public function remover(Carrito $carrito, int $itemId): string
    {
        $this->carritoService->assertOperable($carrito);

        $item = ItemCarrito::where('id', $itemId)
            ->where('carrito_id', $carrito->id)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->first();

        if (!$item) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Ítem {$itemId} no encontrado.");
        }

        $item->update([
            'estado_item' => ItemCarrito::ESTADO_REMOVIDO,
            'removed_at'  => now(),
        ]);

        $this->recalcularTotal($carrito);
        $carrito->refresh();

        Bitacora::create([
            'accion'       => Bitacora::ACCION_ITEM_REMOVIDO,
            'modulo_id'    => $carrito->modulo_id,
            'carrito_uuid' => $carrito->uuid,
            'payload'      => ['item_id' => $itemId],
        ]);

        return $carrito->total;
    }

    /**
     * @throws CartBusinessException  Si la categoría no permite devolución.
     * @throws CartStateException
     */
    public function devolver(Carrito $carrito, int $itemId): ItemCarrito
    {
        $item = ItemCarrito::where('id', $itemId)
            ->where('carrito_id', $carrito->id)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->with('categoria.reglas')
            ->first();

        if (!$item) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Ítem {$itemId} no encontrado.");
        }

        $permiteDevolucion = $this->getRegla($item->categoria, 'permite_devolucion', false);
        if (!$permiteDevolucion) {
            throw new CartBusinessException(
                "La categoría '{$item->categoria->slug}' no permite devolución."
            );
        }

        $item->update(['estado_item' => ItemCarrito::ESTADO_DEVUELTO]);

        $this->recalcularTotal($carrito);

        Bitacora::create([
            'accion'       => Bitacora::ACCION_ITEM_DEVUELTO,
            'modulo_id'    => $carrito->modulo_id,
            'carrito_uuid' => $carrito->uuid,
            'payload'      => ['item_id' => $itemId],
        ]);

        return $item->fresh();
    }

    private function recalcularTotal(Carrito $carrito): void
    {
        $total = ItemCarrito::where('carrito_id', $carrito->id)
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
            ->get()
            ->sum(fn($i) => (float) $i->precio_unitario * $i->cantidad);

        $carrito->update(['total' => number_format($total, 2, '.', '')]);
    }

    private function getRegla(Categoria $categoria, string $clave, mixed $default): mixed
    {
        $categoria->loadMissing('reglas');
        $regla = $categoria->reglas->firstWhere('clave', $clave);
        return $regla ? $regla->valorCasteado() : $default;
    }
}
