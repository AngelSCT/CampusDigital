<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoTienda extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'usuario_id',
        'destinatario_id',
        'total',
        'estado',
        'metodo_pago',
        'gracia_hasta',
        'notificado_destinatario',
        'pago_token',
        'pago_expira_en',
    ];

    protected $casts = [
        'total'                   => 'decimal:2',
        'gracia_hasta'            => 'datetime',
        'notificado_destinatario' => 'boolean',
        'pago_expira_en'          => 'datetime',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
    ];

    /** ¿Puede el comprador aún cancelar bajo el período de gracia 'Oops'? */
    public function dentroDeGracia(): bool
    {
        return $this->gracia_hasta !== null
            && now()->isBefore($this->gracia_hasta)
            && ! $this->notificado_destinatario;
    }

    const ESTADOS = ['pendiente', 'pagado', 'cancelado', 'entregado'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class, 'pedido_id');
    }
}
