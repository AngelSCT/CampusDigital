<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recarga extends Model
{
    use SoftDeletes;

    protected $table = 'recargas';

    protected $fillable = [
        'usuario_id',
        'monto',
        'metodo_pago',
        'estado',
        'referencia',
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

    const ESTADOS = ['pendiente', 'exitosa', 'fallida'];
    const METODOS = ['tarjeta', 'transferencia', 'efectivo', 'billetera_digital'];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Polimorfismo: esta recarga tiene movimientos asociados
    public function movimiento()
    {
        return $this->morphMany(Movimiento::class, 'referencia');
    }

    public function comprobante()
    {
        return $this->morphOne(Comprobante::class, 'referencia');
    }

    // Scopes útiles
    public function scopeExitosas($query)
    {
        return $query->where('estado', 'exitosa');
    }

    public function scopeFallidas($query)
    {
        return $query->where('estado', 'fallida');
    }

    public function scopeDelUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('created_at');
    }

    // Métodos helper
    public function esExitosa()
    {
        return $this->estado === 'exitosa';
    }

    public function esFallida()
    {
        return $this->estado === 'fallida';
    }

    public function esPendiente()
    {
        return $this->estado === 'pendiente';
    }

    public function generarFolio()
    {
        return 'WEB-' . strtoupper(uniqid());
    }
}