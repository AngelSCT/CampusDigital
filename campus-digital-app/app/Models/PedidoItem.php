<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo PedidoItem — un renglón de detalle dentro de un pedido.
 *
 * Cada PedidoItem es un snapshot histórico del producto comprado:
 * guarda el nombre y precio al momento de la venta, para que cambios
 * futuros en el catálogo (M4.3) no afecten pedidos viejos.
 */
class PedidoItem extends Model
{
    protected $table = 'pedido_item';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'nombre_producto',
        'cantidad',
        'precio_unitario',
        'aplica_iva',
        'subtotal',
        'iva_monto',
        'total_linea',
        'meta_json',
    ];

    protected $casts = [
        'cantidad'        => 'integer',
        'precio_unitario' => 'decimal:2',
        'aplica_iva'      => 'boolean',
        'subtotal'        => 'decimal:2',
        'iva_monto'       => 'decimal:2',
        'total_linea'     => 'decimal:2',
        'meta_json'       => 'array',
    ];

    /**
     * Relación: este item pertenece a un Pedido.
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}