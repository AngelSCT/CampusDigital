<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Módulo 4.7 — Reserva
 *
 * Reserva de un recurso por parte de un usuario en un rango horario.
 */
class Reserva extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'reservas';
    protected $primaryKey = 'id_reserva';
    const DELETED_AT      = 'deleted_at';

    // ── Estados ───────────────────────────────────────────────────────────
    const ESTADO_PENDIENTE  = 'pendiente';
    const ESTADO_CONFIRMADA = 'confirmada';
    const ESTADO_CANCELADA  = 'cancelada';
    const ESTADO_NO_SHOW    = 'no_show';
    const ESTADO_COMPLETADA = 'completada';

    protected $fillable = [
        'id_recurso',
        'id_usuario',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'proposito',
        'cobro_saldo',
        'monto_cobrado',
        'id_usuario_cancelacion',
        'cancelada_at',
        'motivo_cancelacion',
        'check_in_at',
    ];

    protected $casts = [
        'id_recurso'             => 'integer',
        'id_usuario'             => 'integer',
        'id_usuario_cancelacion' => 'integer',
        'fecha_inicio'           => 'datetime',
        'fecha_fin'              => 'datetime',
        'cancelada_at'           => 'datetime',
        'check_in_at'            => 'datetime',
        'cobro_saldo'            => 'boolean',
        'monto_cobrado'          => 'decimal:2',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
        'deleted_at'             => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function recurso()
    {
        return $this->belongsTo(Recurso::class, 'id_recurso', 'id_recurso');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }

    public function usuarioCancela()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_cancelacion', 'id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function estaActiva(): bool
    {
        return in_array($this->estado, [self::ESTADO_PENDIENTE, self::ESTADO_CONFIRMADA], true);
    }

    public function estaVigente(): bool
    {
        return $this->estaActiva()
            && $this->fecha_inicio->isFuture();
    }

    public function duracionHoras(): float
    {
        $segundos = $this->fecha_fin->diffInSeconds($this->fecha_inicio);
        return round($segundos / 3600, 2);
    }

    public function getEstadosDisponibles(): array
    {
        return [
            self::ESTADO_PENDIENTE  => 'Pendiente',
            self::ESTADO_CONFIRMADA => 'Confirmada',
            self::ESTADO_CANCELADA  => 'Cancelada',
            self::ESTADO_NO_SHOW    => 'No Show',
            self::ESTADO_COMPLETADA => 'Completada',
        ];
    }
}
