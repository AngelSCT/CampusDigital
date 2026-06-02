<?php

namespace App\Services\Catalogo;

use App\Models\Catalogo\Catalogo;
use App\Exceptions\Catalogo\CategoryUnavailableException;
use App\Exceptions\Catalogo\PriceUnavailableException;
use App\Exceptions\Catalogo\OutOfStockException;
use Illuminate\Support\Str;

class CatalogoCartResolver
{
    /**
     * Mapa exacto de nombres de categoría → slug de carrito.
     */
    private const SLUG_MAP = [
        'cafeteria'            => 'cafeteria',
        'copias e impresiones' => 'copias',
        'tramites'             => 'tramites',
        'souvenirs'            => 'souvenirs',
        'servicios internos'   => 'servicios',
        'servicios'            => 'servicios',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Método 1: slugDesdeCategoria
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normaliza el nombre de la categoría y retorna el slug de carrito,
     * o null si la categoría no tiene slug asignado.
     */
    public function slugDesdeCategoria(?string $nombre): ?string
    {
        $normalizado = strtolower(Str::ascii(trim($nombre ?? '')));

        return self::SLUG_MAP[$normalizado] ?? null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Método 2: parseReferencia
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Parsea una referencia externa con formato "CAT-{id}" y retorna el id
     * como entero, o null si el formato no coincide.
     */
    public function parseReferencia(string $ref): ?int
    {
        if (preg_match('/^CAT-(\d+)$/', $ref, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Método 3: stockDisponible
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Determina si hay stock suficiente para la cantidad solicitada.
     *
     * - Servicios: siempre disponibles (no tienen stock físico).
     * - Productos con fila en inventario: stock_actual >= $cantidad.
     * - Productos SIN fila en inventario: respeta la config
     *   cart.catalogo.permitir_sin_inventario (default true).
     */
    public function stockDisponible(Catalogo $catalogo, int $cantidad): bool
    {
        if ($catalogo->tipo === 'servicio') {
            return true;
        }

        // Producto con inventario registrado
        if ($catalogo->inventario !== null) {
            return $catalogo->inventario->stock_actual >= $cantidad;
        }

        // Producto sin fila de inventario → política configurable
        return (bool) config('cart.catalogo.permitir_sin_inventario', true);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Método 4: construirPayloadItem
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Construye el array listo para enviarse al endpoint del Carrito.
     *
     * @throws CategoryUnavailableException  Si la categoría no tiene slug.
     * @throws PriceUnavailableException     Si no hay precio vigente.
     * @throws OutOfStockException           Si no hay stock suficiente.
     */
    public function construirPayloadItem(Catalogo $catalogo, int $cantidad = 1): array
    {
        // 1. Slug de categoría
        $slug = $this->slugDesdeCategoria($catalogo->categoria?->nombre);
        if ($slug === null) {
            throw new CategoryUnavailableException();
        }

        // 2. Precio vigente
        $precio = $catalogo->precioVigenteValor();
        if ($precio === null) {
            throw new PriceUnavailableException();
        }

        // 3. Stock
        if (! $this->stockDisponible($catalogo, $cantidad)) {
            throw new OutOfStockException();
        }

        // 4. Payload
        return [
            'categoria_slug'     => $slug,
            'referencia_externa' => 'CAT-' . $catalogo->id_catalogo,
            'nombre'             => $catalogo->nombre,
            'precio_unitario'    => number_format((float) $precio, 2, '.', ''),
            'cantidad'           => $cantidad,
            'metadata'           => [
                'id_catalogo'     => $catalogo->id_catalogo,
                'tipo'            => $catalogo->tipo,
                'origen'          => 'catalogo',
                'stock_rastreado' => $catalogo->inventario !== null,
                'sin_inventario'  => $catalogo->tipo === 'producto' && $catalogo->inventario === null,
                'stock_snapshot'  => $catalogo->inventario?->stock_actual,
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Método 5: estadoCarrito
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fuente de verdad de disponibilidad de un ítem para el carrito.
     * NO lanza excepciones; siempre retorna un array descriptivo.
     *
     * @return array{
     *   cart_disponible: bool,
     *   stock_rastreado: bool,
     *   stock_actual: int|null,
     *   motivo_no_disponible: string|null,
     * }
     */
    public function estadoCarrito(Catalogo $catalogo, int $cantidad = 1): array
    {
        $stockRastreado = $catalogo->inventario !== null;
        $stockActual    = $catalogo->inventario?->stock_actual;

        // Ítem inactivo
        if ($catalogo->activo === false) {
            return [
                'cart_disponible'      => false,
                'stock_rastreado'      => $stockRastreado,
                'stock_actual'         => $stockActual,
                'motivo_no_disponible' => 'INACTIVO',
            ];
        }

        // Categoría sin slug
        if ($this->slugDesdeCategoria($catalogo->categoria?->nombre) === null) {
            return [
                'cart_disponible'      => false,
                'stock_rastreado'      => $stockRastreado,
                'stock_actual'         => $stockActual,
                'motivo_no_disponible' => 'CATEGORY_UNAVAILABLE',
            ];
        }

        // Sin precio vigente
        if ($catalogo->precioVigenteValor() === null) {
            return [
                'cart_disponible'      => false,
                'stock_rastreado'      => $stockRastreado,
                'stock_actual'         => $stockActual,
                'motivo_no_disponible' => 'PRICE_UNAVAILABLE',
            ];
        }

        // Sin stock suficiente
        if (! $this->stockDisponible($catalogo, $cantidad)) {
            return [
                'cart_disponible'      => false,
                'stock_rastreado'      => $stockRastreado,
                'stock_actual'         => $stockActual,
                'motivo_no_disponible' => 'OUT_OF_STOCK',
            ];
        }

        // Todo OK
        return [
            'cart_disponible'      => true,
            'stock_rastreado'      => $stockRastreado,
            'stock_actual'         => $stockActual,
            'motivo_no_disponible' => null,
        ];
    }
}
