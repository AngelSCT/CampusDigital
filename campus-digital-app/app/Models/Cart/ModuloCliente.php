<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuloCliente extends Model
{
    protected $table = 'cart_modulos_clientes';

    protected $fillable = [
        'solicitud_id',
        'nombre',
        'slug',
        'tipo_modulo',
        'categorias_autorizadas',
        'activo',
    ];

    protected $casts = [
        'categorias_autorizadas' => 'array',
        'activo'                 => 'boolean',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudModulo::class, 'solicitud_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(TokenModulo::class, 'modulo_id');
    }

    public function carritos(): HasMany
    {
        return $this->hasMany(Carrito::class, 'modulo_id');
    }

    public function bitacora(): HasMany
    {
        return $this->hasMany(Bitacora::class, 'modulo_id');
    }

    public function conciliaciones(): HasMany
    {
        return $this->hasMany(ConciliacionPendiente::class, 'modulo_id');
    }
}
