<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCarrito extends Model
{
    protected $table = 'cart_items_carrito';

    protected $fillable = [
        'carrito_id',
        'categoria_id',
        'referencia_externa',
        'nombre',
        'precio_unitario',
        'cantidad',
        'duracion_horas',
        'estado_item',
        'metadata',
        'added_at',
        'removed_at',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'cantidad'        => 'integer',
        'duracion_horas'  => 'integer',
        'metadata'        => 'array',
        'added_at'        => 'datetime',
        'removed_at'      => 'datetime',
    ];

    const ESTADO_ACTIVO   = 'activo';
    const ESTADO_REMOVIDO = 'removido';
    const ESTADO_DEVUELTO = 'devuelto';

    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function subtotal(): string
    {
        return number_format((float) $this->precio_unitario * $this->cantidad, 2, '.', '');
    }
}
