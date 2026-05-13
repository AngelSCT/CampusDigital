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
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $fecha = now()->format('Ymd');
            $prefijo = "PED-{$fecha}-";

            // lockForUpdate evita que dos peticiones simultáneas lean el mismo "último folio"
            $ultimo = static::where('numero_folio', 'like', "{$prefijo}%")
                ->lockForUpdate()
                ->orderByDesc('numero_folio')
                ->first();

            if ($ultimo) {
                // Extrae el consecutivo del último folio (los últimos 4 dígitos)
                $consecutivo = ((int) substr($ultimo->numero_folio, -4)) + 1;
            } else {
                $consecutivo = 1;
            }

            return sprintf('%s%04d', $prefijo, $consecutivo);
        });
    }

    public function historial()
    {
        return $this->hasMany(PedidoHistorial::class, 'pedido_id');
    }

    /**
     * Relación: un pedido tiene muchos items (detalle por producto).
     */
    public function items()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}