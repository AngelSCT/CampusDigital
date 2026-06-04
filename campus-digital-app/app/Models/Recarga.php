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
        'razon_fallo',
        'saldo_movimiento_id',
        'meta_json',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'meta_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EXITOSO = 'exitosa';
    const ESTADO_FALLIDO = 'fallida';

    const METODOS_PAGO = [
        'tarjeta',
        'transferencia',
        'efectivo',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function comprobante()
    {
        return $this->morphOne(Comprobante::class, 'referencia');
    }

    public function saldoMovimiento()
    {
        return $this->belongsTo(SaldoMovimiento::class, 'saldo_movimiento_id');
    }

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
}