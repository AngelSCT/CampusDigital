<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimientos';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_catalogo',
        'cantidad',
        'fecha'
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'id_catalogo');
    }
}