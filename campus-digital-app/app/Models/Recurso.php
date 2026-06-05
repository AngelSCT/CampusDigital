<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Módulo 4.7 — Recurso Reservable
 *
 * Representa cualquier recurso físico que puede ser reservado:
 * salas, laboratorios o equipos.
 */
class Recurso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'recursos';
    protected $primaryKey = 'id_recurso';
    const DELETED_AT      = 'deleted_at';

    // ── Tipos ─────────────────────────────────────────────────────────────
    const TIPO_SALA        = 'sala';
    const TIPO_LABORATORIO = 'laboratorio';
    const TIPO_EQUIPO      = 'equipo';

    // ── Estados ───────────────────────────────────────────────────────────
    const ESTADO_DISPONIBLE   = 'disponible';
    const ESTADO_MANTENIMIENTO = 'mantenimiento';
    const ESTADO_INACTIVO     = 'inactivo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'capacidad',
        'id_ubicacion',
        'estado',
        'costo_por_hora',
        'imagen_url',
        'horarios',
    ];

    protected $casts = [
        'capacidad'      => 'integer',
        'id_ubicacion'   => 'integer',
        'costo_por_hora' => 'decimal:2',
        'horarios'       => 'array',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_recurso', 'id_recurso');
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class, 'id_recurso', 'id_recurso');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function estaDisponible(): bool
    {
        return $this->estado === self::ESTADO_DISPONIBLE;
    }

    public function tieneCosto(): bool
    {
        return $this->costo_por_hora > 0;
    }

    public function getTiposDisponibles(): array
    {
        return [
            self::TIPO_SALA        => 'Sala',
            self::TIPO_LABORATORIO => 'Laboratorio',
            self::TIPO_EQUIPO      => 'Equipo',
        ];
    }

    public function getEstadosDisponibles(): array
    {
        return [
            self::ESTADO_DISPONIBLE    => 'Disponible',
            self::ESTADO_MANTENIMIENTO => 'Mantenimiento',
            self::ESTADO_INACTIVO      => 'Inactivo',
        ];
    }
}
