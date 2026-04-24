<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimientos';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'monto',
        'estado',
        'modulo',
        'concepto',
        'saldo_anterior',
        'saldo_nuevo',
        'referencia_type',
        'referencia_id',
    ];

    protected $casts = [
        'monto'          => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_nuevo'    => 'decimal:2',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    // Módulos disponibles para simulación
    const MODULOS = ['cafeteria', 'copias', 'souvenirs', 'biblioteca', 'acceso'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function referenciable()
    {
        return $this->morphTo();
    }

    /** Indica si el movimiento es un cargo (gasto) */
    public function esCargo(): bool
    {
        return $this->tipo === 'pago';
    }

    /** Indica si el movimiento es un abono (recarga) */
    public function esAbono(): bool
    {
        return $this->tipo === 'recarga';
    }
}