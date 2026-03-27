<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Precio extends Model
{
    protected $table = 'precios';
    protected $primaryKey = 'id_precio';
    public $timestamps = false;

    protected $fillable = [
        'id_catalogo',
        'precio',
        'fecha_inicio',
        'fecha_fin'
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'id_catalogo');
    }
}