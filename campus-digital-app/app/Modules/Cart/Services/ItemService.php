<?php

namespace App\Modules\Cart\Services;

use App\Models\Cart\Bitacora;
use App\Models\Cart\Carrito;
use App\Models\Cart\Categoria;
use App\Models\Cart\ItemCarrito;
use App\Models\Cart\ModuloCliente;
use App\Models\Cart\ReglaCategoria;
use App\Modules\Cart\Exceptions\CartBusinessException;
use App\Modules\Cart\Exceptions\CartStateException;
use App\Modules\Cart\Exceptions\ScopeDeniedException;
use Illuminate\Support\Facades\DB;

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
        // ── FUERA de TX: validaciones de solo lectura (fail fast) ─────────────────
        $this->carritoService->assertOperable($carrito);

        $slug = $data['categoria_slug'];
        if (!in_array($slug, $jwtPayload['categorias_autorizadas'] ?? [], true)) {
            throw new ScopeDeniedException();
        }

        $categoria = Categoria::where('slug', $slug)->first();
        if (!$categoria || !$categoria->activa) {
            throw new CartBusinessException("Categoría '{$slug}' no disponible.");
        }

        $cantidad = (int) $data['cantidad'];
        $cantMax  = $this->getRegla($categoria, ReglaCategoria::CLAVE_CANTIDAD_MAXIMA, PHP_INT_MAX);
        $durMax   = $this->getRegla($categoria, ReglaCategoria::CLAVE_DURACION_MAXIMA_HORAS, null);

        if ($durMax !== null && isset($data['duracion_horas']) && $data['duracion_horas'] > $durMax) {
            throw new CartBusinessException("La duración máxima para '{$slug}' es {$durMax} horas.");
        }

        // Validación de precio por reglas de categoría
        $this->validarPrecioPorCategoria($categoria, $slug, (float) $data['precio_unitario']);

        // ── DENTRO de TX: escrituras atómicas con lock ────────────────────────────
        return DB::transaction(function () use ($carrito, $modulo, $categoria, $data, $cantidad, $cantMax, $slug) {
            // Re-leer con lock — si el carrito cambió de estado (ej. procesando_checkout) antes
            // de que entráramos a la TX, assertOperable() lanzará CartStateException y la TX
            // hará rollback antes de persistir nada.
            $carritoFresh = Carrito::where('id', $carrito->id)->lockForUpdate()->firstOrFail();
            $this->carritoService->assertOperable($carritoFresh);

            // Lock sobre ítem existente para upsert seguro (evita race condition entre dos agregar)
            $existing = ItemCarrito::where('carrito_id', $carritoFresh->id)
                ->where('referencia_externa', $data['referencia_externa'])
                ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $nuevaCantidad = $existing->cantidad + $cantidad;
                if ($nuevaCantidad > $cantMax) {
                    throw new CartBusinessException(
                        "La cantidad resultante ({$nuevaCantidad}) supera el máximo permitido ({$cantMax})."
                    );
                }
                $existing->update(['cantidad' => $nuevaCantidad]);
                $accion = 'incrementado';
                $item   = $existing->fresh();
            } else {
                if ($cantidad > $cantMax) {
                    throw new CartBusinessException(
                        "La cantidad ({$cantidad}) supera el máximo permitido ({$cantMax}) para '{$slug}'."
                    );
                }
                $item = ItemCarrito::create([
                    'carrito_id'         => $carritoFresh->id,
                    'categoria_id'       => $categoria->id,
                    'referencia_externa' => $data['referencia_externa'],
                    'nombre'             => $data['nombre'],
                    'precio_unitario'    => $data['precio_unitario'],
                    'cantidad'           => $cantidad,
                    'duracion_horas'     => $data['duracion_horas'] ?? null,
                    'metadata'           => $data['metadata'] ?? null,
                    'added_at'           => now(),
                ]);
                $accion = 'creado';
            }

            $this->recalcularTotal($carritoFresh);

            Bitacora::create([
                'accion'       => Bitacora::ACCION_ITEM_AGREGADO,
                'modulo_id'    => $modulo->id,
                'carrito_uuid' => $carritoFresh->uuid,
                'payload'      => ['referencia_externa' => $data['referencia_externa'], 'accion' => $accion],
            ]);

            $carritoFresh->refresh();

            return [
                'accion'            => $accion,
                'item_id'           => $item->id,
                'carrito_uuid'      => $carritoFresh->uuid,
                'cantidad_actual'   => $item->fresh()->cantidad,
                'total_actualizado' => $carritoFresh->total,
            ];
        });
    }

    /** @throws CartStateException */
    public function remover(Carrito $carrito, int $itemId): string
    {
        // Validación rápida fuera de TX (fail fast)
        $this->carritoService->assertOperable($carrito);

        return DB::transaction(function () use ($carrito, $itemId) {
            $carritoFresh = Carrito::where('id', $carrito->id)->lockForUpdate()->firstOrFail();
            $this->carritoService->assertOperable($carritoFresh);

            $item = ItemCarrito::where('id', $itemId)
                ->where('carrito_id', $carritoFresh->id)
                ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
                ->first();

            if (!$item) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Ítem {$itemId} no encontrado.");
            }

            $item->update([
                'estado_item' => ItemCarrito::ESTADO_REMOVIDO,
                'removed_at'  => now(),
            ]);

            $this->recalcularTotal($carritoFresh);

            Bitacora::create([
                'accion'       => Bitacora::ACCION_ITEM_REMOVIDO,
                'modulo_id'    => $carritoFresh->modulo_id,
                'carrito_uuid' => $carritoFresh->uuid,
                'payload'      => ['item_id' => $itemId],
            ]);

            $carritoFresh->refresh();
            return $carritoFresh->total;
        });
    }

    /**
     * @throws CartBusinessException  Si la categoría no permite devolución.
     * @throws CartStateException
     */
    public function devolver(Carrito $carrito, int $itemId): ItemCarrito
    {
        return DB::transaction(function () use ($carrito, $itemId) {
            $carritoFresh = Carrito::where('id', $carrito->id)->lockForUpdate()->firstOrFail();
            $this->carritoService->assertOperable($carritoFresh);

            $item = ItemCarrito::where('id', $itemId)
                ->where('carrito_id', $carritoFresh->id)
                ->where('estado_item', ItemCarrito::ESTADO_ACTIVO)
                ->with('categoria.reglas')
                ->first();

            if (!$item) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Ítem {$itemId} no encontrado.");
            }

            $permiteDevolucion = $this->getRegla($item->categoria, ReglaCategoria::CLAVE_PERMITE_DEVOLUCION, false);
            if (!$permiteDevolucion) {
                throw new CartBusinessException(
                    "La categoría '{$item->categoria->slug}' no permite devolución."
                );
            }

            $item->update(['estado_item' => ItemCarrito::ESTADO_DEVUELTO]);

            $this->recalcularTotal($carritoFresh);

            Bitacora::create([
                'accion'       => Bitacora::ACCION_ITEM_DEVUELTO,
                'modulo_id'    => $carritoFresh->modulo_id,
                'carrito_uuid' => $carritoFresh->uuid,
                'payload'      => ['item_id' => $itemId],
            ]);

            return $item->fresh();
        });
    }

    private function validarPrecioPorCategoria(Categoria $categoria, string $slug, float $precioEnviado): void
    {
        $permitePrecioCero = $this->getRegla($categoria, ReglaCategoria::CLAVE_PERMITE_PRECIO_CERO, false);
        $precioMinimo      = $this->getRegla($categoria, ReglaCategoria::CLAVE_PRECIO_MINIMO, null);

        // Si la categoría no permite precio cero y no tiene regla de precio mínimo,
        // aplicar 0.01 como mínimo implícito (evita precios sub-centavo).
        if (!$permitePrecioCero) {
            $precioMinimo = $precioMinimo ?? '0.01';
        }

        if (!$permitePrecioCero && $precioEnviado === 0.0) {
            throw new CartBusinessException("La categoría '{$slug}' no permite precio cero.");
        }

        if ($precioMinimo !== null && $precioEnviado < (float) $precioMinimo) {
            throw new CartBusinessException(
                "El precio mínimo para la categoría '{$slug}' es {$precioMinimo}."
            );
        }
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
