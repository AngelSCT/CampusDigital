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
        'referencia_type',
        'referencia_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function referencia()
    {
        return $this->morphTo(__FUNCTION__, 'referencia_type', 'referencia_id');
    }
}