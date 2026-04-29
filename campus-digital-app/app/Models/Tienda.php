<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tienda extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tienda';

    const TIPO_CAFETERIA = 'cafeteria';
    const TIPO_PAPELERIA = 'papeleria';
    const TIPO_TIENDA = 'tienda';

    const TIPOS = [
        self::TIPO_CAFETERIA => 'Cafetería',
        self::TIPO_PAPELERIA => 'Papelería / Copias',
        self::TIPO_TIENDA    => 'Tienda de Conveniencia',
    ];

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'ubicacion',
        'imagen_url',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'tienda_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'tienda_id');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'tienda_id');
    }
}
