<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pedido';

    protected $fillable = [
        'usuario_id',
        'numero_folio',
        'estado',
        'modulo',
        'tienda_id',
        'tipo_entrega',
        'repartidor_id',
        'total',
        'descripcion',
        'notas',
        'operador_usuario_id',
        'confirmado_con_tarjeta',
        'confirmado_at',
        'tarjeta_lectura_id',
        'cobrado_de_saldo',
        'saldo_movimiento_id',
        'meta_json',
    ];

    protected $casts = [
        'total'                  => 'decimal:2',
        'confirmado_con_tarjeta' => 'boolean',
        'cobrado_de_saldo'       => 'boolean',
        'confirmado_at'          => 'datetime',
        'meta_json'              => 'array',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
        'deleted_at'             => 'datetime',
    ];

    const ESTADOS = ['creado', 'aceptado', 'en_proceso', 'listo', 'entregado', 'cancelado'];
    const MODULOS  = ['cafeteria', 'copias', 'souvenirs', 'biblioteca', 'otro'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function operador()
    {
        return $this->belongsTo(Usuario::class, 'operador_usuario_id');
    }


    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }

    public function repartidor()
    {
        return $this->belongsTo(Usuario::class, 'repartidor_id');
    }

    public function tarjetaLectura()
    {
        return $this->belongsTo(TarjetaLectura::class, 'tarjeta_lectura_id');
    }

    public function saldoMovimiento()
    {
        return $this->belongsTo(SaldoMovimiento::class, 'saldo_movimiento_id');
    }

    public function estaListo(): bool
    {
        return $this->estado === 'listo';
    }

    public function puedeEntregarse(): bool
    {
        return in_array($this->estado, ['listo', 'aceptado', 'en_proceso']);
    }

    public static function generarFolio(): string
    {
        $fecha     = now()->format('Ymd');
        $siguiente = static::whereDate('created_at', today())->count() + 1;
        return sprintf('PED-%s-%04d', $fecha, $siguiente);
    }
}