<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecioVendedor extends Model
{
    protected $table = 'precios_vendedor';
    protected $primaryKey = 'id_precio_v';
    public $timestamps = false;

    protected $fillable = [
        'id_cv',
        'precio',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function catalogoVendedor()
    {
        return $this->belongsTo(CatalogoVendedor::class, 'id_cv');
    }
}
