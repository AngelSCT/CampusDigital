<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConciliacionPendiente extends Model
{
    protected $table = 'cart_conciliaciones_pendientes';

    protected $fillable = [
        'carrito_uuid',
        'modulo_id',
        'usuario_ref',
        'monto',
        'intentos',
        'ultimo_intento_at',
        'proximo_intento_at',
        'estado_conciliacion',
        'respuesta_saldo',
    ];

    protected $casts = [
        'monto'              => 'decimal:2',
        'intentos'           => 'integer',
        'ultimo_intento_at'  => 'datetime',
        'proximo_intento_at' => 'datetime',
        'respuesta_saldo'    => 'array',
    ];

    const ESTADO_PENDIENTE              = 'pendiente';
    const ESTADO_PROCESANDO             = 'procesando';       // job en vuelo: TX1 marcó, HTTP aún no respondió
    const ESTADO_EXITOSA                = 'exitosa';
    const ESTADO_DEUDA                  = 'deuda';
    const ESTADO_REQUIERE_REVISION      = 'requiere_revision_manual';

    // Backoff: 1min → 5min → 15min → 1h → 6h → revisión manual (sección C5).
    const BACKOFF_MINUTOS = [1, 5, 15, 60, 360];

    public function carrito(): BelongsTo
    {
        return $this->belongsTo(Carrito::class, 'carrito_uuid', 'uuid');
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(ModuloCliente::class, 'modulo_id');
    }

    /** Calcula el próximo timestamp de reintento según backoff. Null = pasar a revisión manual. */
    public function proximoIntento(): ?\Carbon\Carbon
    {
        $minutos = self::BACKOFF_MINUTOS[$this->intentos] ?? null;

        return $minutos !== null ? now()->addMinutes($minutos) : null;
    }
}
