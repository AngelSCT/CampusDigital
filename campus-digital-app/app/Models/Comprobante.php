<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comprobante extends Model
{
    use SoftDeletes;

    protected $table = 'comprobantes';

    protected $fillable = [
        'carrito_uuid',
        'ticket_id',
        'folio',
        'usuario_ref',
        'modulo_origen',
        'items',
        'total',
        'estado',
        'fecha_confirmacion',
        'data_raw',
    ];

    protected $casts = [
        'items'              => 'array',
        'data_raw'           => 'array',
        'total'              => 'decimal:2',
        'fecha_confirmacion' => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'deleted_at'         => 'datetime',
    ];

    // ─── Estados ──────────────────────────────────────────────
    const ESTADO_CONFIRMADO = 'confirmado';
    const ESTADO_PENDIENTE  = 'pendiente';
    const ESTADO_CANCELADO  = 'cancelado';

    // ─── Relaciones ───────────────────────────────────────────

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_ref', 'id');
    }

    // Relación polymorphic inversa (para Recarga, Pedido, etc.)
    public function referencia()
    {
        return $this->morphTo();
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function estaConfirmado(): bool
    {
        return $this->estado === self::ESTADO_CONFIRMADO;
    }

    public function totalFormateado(): string
    {
        return '$' . number_format($this->total, 2);
    }
}
