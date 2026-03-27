<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $table = 'promociones';
    protected $primaryKey = 'id_promocion';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'valor',
        'fecha_inicio',
        'fecha_fin',
        'activa',
    ];

    public function catalogo()
    {
        return $this->belongsToMany(
            Catalogo::class,
            'promociones_catalogo',
            'id_promocion',
            'id_catalogo',
            'id_promocion',
            'id_catalogo'
        );
    }

    public function catalogoVendedor()
    {
        return $this->belongsToMany(
            CatalogoVendedor::class,
            'promociones_vendedor',
            'id_promocion',
            'id_cv',
            'id_promocion',
            'id_cv'
        );
    }
}
