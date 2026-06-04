<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaldoMovimiento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'saldo_movimiento';

    protected $fillable = [
        'usuario_id',
        'saldo_monedero_id',
        'tipo',
        'monto',
        'saldo_anterior',
        'saldo_nuevo',
        'modulo',
        'concepto',
        'referencia_tabla',
        'referencia_id',
        'operador_usuario_id',
        'tarjeta_lectura_id',
        'meta_json',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_nuevo' => 'decimal:2',
        'meta_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const TIPO_ABONO = 'abono';
    const TIPO_CARGO = 'cargo';

    const MODULOS = [
        'cafeteria',
        'copias',
        'souvenirs',
        'biblioteca',
        'recarga',
        'rfid',
        'otro',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function monedero()
    {
        return $this->belongsTo(SaldoMonedero::class, 'saldo_monedero_id');
    }

    public function operador()
    {
        return $this->belongsTo(Usuario::class, 'operador_usuario_id');
    }

    public function tarjetaLectura()
    {
        return $this->belongsTo(TarjetaLectura::class, 'tarjeta_lectura_id');
    }

    public function recarga()
    {
        return $this->belongsTo(Recarga::class, 'referencia_id')
            ->where($this->getTable() . '.referencia_tabla', 'recarga');
    }
}