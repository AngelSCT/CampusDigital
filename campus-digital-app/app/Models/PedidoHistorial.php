<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoHistorial extends Model
{
    protected $table = 'pedido_historial';

    protected $fillable = [
        'pedido_id',
        'estado_anterior',
        'estado_nuevo',
        'usuario_id',
        'notas',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}