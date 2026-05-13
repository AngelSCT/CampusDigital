<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recarga extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recargas';

    protected $fillable = [
        'usuario_id',
        'monto',
        'metodo_pago',
        'estado',
        'referencia_pago',
        'razon_fallo',
        'saldo_movimiento_id',
        'meta_json',
    ];

    protected $casts = [
        'monto'      => 'decimal:2',
        'meta_json'  => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Estados posibles
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EXITOSO   = 'exitoso';
    const ESTADO_FALLIDO   = 'fallido';

    // Métodos de pago aceptados
    const METODOS_PAGO = ['tarjeta', 'transferencia', 'efectivo', 'billetera_digital'];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function movimiento()
    {
        return $this->morphMany(Movimiento::class, 'referenciable');
    }

    public function comprobante()
    {
        return $this->morphOne(Comprobante::class, 'referencia');
    }

    public function saldoMovimiento()
    {
        return $this->belongsTo(SaldoMovimiento::class, 'saldo_movimiento_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeExitosas($query)
    {
        return $query->where('estado', self::ESTADO_EXITOSO);
    }

    public function scopeFallidas($query)
    {
        return $query->where('estado', self::ESTADO_FALLIDO);
    }

    public function scopeDelUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('created_at');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function esExitosa(): bool
    {
        return $this->estado === self::ESTADO_EXITOSO;
    }

    public function esFallida(): bool
    {
        return $this->estado === self::ESTADO_FALLIDO;
    }

    public function esPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function generarFolio(): string
    {
        return 'WEB-' . strtoupper(uniqid());
    }
}
