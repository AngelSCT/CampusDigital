<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventario';
    protected $primaryKey = 'id_inventario';
    public $timestamps = false;

    protected $fillable = [
        'id_catalogo',
        'stock_actual',
        'stock_minimo',
        'fecha_actualizacion',
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'id_catalogo');
    }
}
