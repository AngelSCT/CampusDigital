<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Carrito extends Model
{
    protected $table = 'cart_carritos';

    protected $fillable = [
        'uuid',
        'modulo_id',
        'usuario_ref',
        'estado',
        'requiere_saldo',
        'total',
        'metadata',
        'expira_at',
        'confirmed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'requiere_saldo' => 'boolean',
        'total'          => 'decimal:2',
        'metadata'       => 'array',
        'expira_at'      => 'datetime',
        'confirmed_at'   => 'datetime',
        'cancelled_at'   => 'datetime',
    ];

    const ESTADO_ABIERTO                           = 'abierto';
    const ESTADO_CONFIRMADO                        = 'confirmado';
    const ESTADO_CANCELADO                         = 'cancelado';
    const ESTADO_EXPIRADO                          = 'expirado';
    const ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION = 'confirmado_pendiente_conciliacion';
    const ESTADO_REVERTIDO                         = 'revertido';

    /** Estados en los que no se pueden realizar operaciones sobre el carrito. */
    const ESTADOS_TERMINALES = [
        self::ESTADO_CONFIRMADO,
        self::ESTADO_CANCELADO,
        self::ESTADO_EXPIRADO,
        self::ESTADO_CONFIRMADO_PENDIENTE_CONCILIACION,
        self::ESTADO_REVERTIDO,
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(ModuloCliente::class, 'modulo_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemCarrito::class, 'carrito_id');
    }

    public function itemsActivos(): HasMany
    {
        return $this->hasMany(ItemCarrito::class, 'carrito_id')
            ->where('estado_item', ItemCarrito::ESTADO_ACTIVO);
    }

    public function conciliacion(): HasOne
    {
        return $this->hasOne(ConciliacionPendiente::class, 'carrito_uuid', 'uuid');
    }

    public function estaAbierto(): bool
    {
        return $this->estado === self::ESTADO_ABIERTO;
    }

    public function estaEnEstadoTerminal(): bool
    {
        return in_array($this->estado, self::ESTADOS_TERMINALES, true);
    }
}
