<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Módulo 4.7 — Turno
 *
 * Sistema de turnos / cola virtual para atención o recolección.
 */
class Turno extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'turnos';
    protected $primaryKey = 'id_turno';
    const DELETED_AT      = 'deleted_at';

    // ── Tipos ─────────────────────────────────────────────────────────────
    const TIPO_ATENCION    = 'atencion';
    const TIPO_RECOLECCION = 'recoleccion';
    const TIPO_CAFETERIA   = 'cafeteria';
    const TIPO_BIBLIOTECA  = 'biblioteca';
    const TIPO_GENERAL     = 'general';

    // ── Estados ───────────────────────────────────────────────────────────
    const ESTADO_ESPERANDO = 'esperando';
    const ESTADO_LLAMADO   = 'llamado';
    const ESTADO_ATENDIDO  = 'atendido';
    const ESTADO_NO_SHOW   = 'no_show';
    const ESTADO_CANCELADO = 'cancelado';

    // Prefijos por tipo para número de turno
    const PREFIJOS = [
        self::TIPO_ATENCION    => 'A',
        self::TIPO_RECOLECCION => 'R',
        self::TIPO_CAFETERIA   => 'C',
        self::TIPO_BIBLIOTECA  => 'B',
        self::TIPO_GENERAL     => 'G',
    ];

    protected $fillable = [
        'id_usuario',
        'tipo_turno',
        'numero_turno',
        'estado',
        'posicion',
        'id_recurso',
        'pedido_referencia',
        'notas',
        'llamado_at',
        'atendido_at',
        'cancelado_at',
    ];

    protected $casts = [
        'id_usuario'  => 'integer',
        'posicion'    => 'integer',
        'id_recurso'  => 'integer',
        'llamado_at'  => 'datetime',
        'atendido_at' => 'datetime',
        'cancelado_at'=> 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }

    public function recurso()
    {
        return $this->belongsTo(Recurso::class, 'id_recurso', 'id_recurso');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function estaActivo(): bool
    {
        return in_array($this->estado, [self::ESTADO_ESPERANDO, self::ESTADO_LLAMADO], true);
    }

    public function getTiposDisponibles(): array
    {
        return [
            self::TIPO_ATENCION    => 'Atención',
            self::TIPO_RECOLECCION => 'Recolección',
            self::TIPO_CAFETERIA   => 'Cafetería',
            self::TIPO_BIBLIOTECA  => 'Biblioteca',
            self::TIPO_GENERAL     => 'General',
        ];
    }

    public function getEstadosDisponibles(): array
    {
        return [
            self::ESTADO_ESPERANDO => 'Esperando',
            self::ESTADO_LLAMADO   => 'Llamado',
            self::ESTADO_ATENDIDO  => 'Atendido',
            self::ESTADO_NO_SHOW   => 'No Show',
            self::ESTADO_CANCELADO => 'Cancelado',
        ];
    }
}
