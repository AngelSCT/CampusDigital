<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TarjetaUniversitaria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tarjeta_universitaria';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'usuario_id',
        'uid',
        'estado',
        'pin_hash',
        'motivo_bloqueo',
        'registrado_por_usuario_id',
        'bloqueado_por_usuario_id',
        'bloqueado_at',
        'meta_json',
    ];

    protected $casts = [
        'bloqueado_at' => 'datetime',
        'meta_json'    => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    /* ─── Relaciones ─────────────────────────────────────── */

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por_usuario_id');
    }

    public function bloqueadoPor()
    {
        return $this->belongsTo(Usuario::class, 'bloqueado_por_usuario_id');
    }

    public function lecturas()
    {
        return $this->hasMany(TarjetaLectura::class, 'tarjeta_id');
    }

    // NUEVO: acceso directo al monedero del dueño de la tarjeta
    public function monedero()
    {
        return $this->hasOneThrough(
            SaldoMonedero::class,
            Usuario::class,
            'id',          // FK en usuario
            'usuario_id',  // FK en saldo_monedero
            'usuario_id',  // FK local en tarjeta_universitaria
            'id'           // PK en usuario
        );
    }

    /* ─── Helpers ────────────────────────────────────────── */

    public function estaActiva(): bool
    {
        return $this->estado === 'activa';
    }

    public function estaBloqueada(): bool
    {
        return in_array($this->estado, ['bloqueada', 'perdida', 'cancelada']);
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'activa'    => 'success',
            'bloqueada' => 'danger',
            'perdida'   => 'warning',
            'cancelada' => 'secondary',
            default     => 'secondary',
        };
    }
}