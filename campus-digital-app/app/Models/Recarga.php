<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recarga extends Model
{

    protected $fillable = [
        'usuario_id',
        'monto',
        'metodo_pago',
        'estado'
    ];

    public function user()
{
    return $this->belongsTo(User::class);
}

public function comprobante()
{
    return $this->morphOne(Comprobante::class, 'referencia');
}

public function movimiento()
{
    return $this->morphOne(Movimiento::class, 'referencia');
}
}

