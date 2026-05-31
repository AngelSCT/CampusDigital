<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    protected $table = 'vendedores';
    protected $primaryKey = 'id_vendedor';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'descripcion',
        'activo',
        'fecha_registro',
    ];

    /**
     * Items del catálogo personalizados de este vendedor.
     *
     * Relación requerida para que withCount('catalogoItems') funcione
     * en el endpoint GET /api/catalogo-integracion/vendedores.
     *
     * AGREGADO — integración módulo 4.3 → 4.9
     */
    public function catalogoItems()
    {
        return $this->hasMany(CatalogoVendedor::class, 'id_vendedor', 'id_vendedor');
    }
}
