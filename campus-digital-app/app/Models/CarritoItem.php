<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarritoItem extends Model
{
    use HasFactory;

    protected $table = 'carrito_items';

    protected $fillable = [
        'usuario_id',
        'producto_id',
        'cantidad',
        'guardado_para_despues',
        'en_wishlist',
        'motivo_movimiento',
        'es_regalo',
        'mensaje_dedicatorio',
        'estado_regalo',
        'fecha_expiracion_regalo',
        'reservado_hasta',
        'regalo_hash',
        'destinatario_id',
        'ultima_actividad_at',
    ];

    protected $casts = [
        'cantidad'               => 'integer',
        'guardado_para_despues'  => 'boolean',
        'en_wishlist'            => 'boolean',
        'es_regalo'               => 'boolean',
        'fecha_expiracion_regalo' => 'datetime',
        'reservado_hasta'         => 'datetime',
        'ultima_actividad_at'    => 'datetime',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    // Relaciones

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Scope: ítems sin actividad por más de 4 días (candidatos a limpieza automática)

    public function scopeInactivos(Builder $query, int $dias = 4): Builder
    {
        return $query->where('ultima_actividad_at', '<=', now()->subDays($dias));
    }
}
