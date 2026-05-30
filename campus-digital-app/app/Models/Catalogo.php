<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    protected $table = 'catalogo';
    protected $primaryKey = 'id_catalogo';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'id_categoria',
        'aplica_iva',
        'id_impuesto',
        'activo'
    ];

    public function areas()
{
    return $this->belongsToMany(
        Area::class,
        'catalogo_area',
        'id_catalogo',
        'id_area'
    );
}

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function inventario()
    {
        return $this->hasOne(Inventario::class, 'id_catalogo', 'id_catalogo');
    }
}