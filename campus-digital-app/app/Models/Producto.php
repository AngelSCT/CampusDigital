<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'precio',
        'stock',
        'categoria',
        'tienda',
        'tipo',
        'fecha_inicio_evento',
        'limite_por_usuario',
        'imagen_url',
        'es_regalo',
    ];

    protected $casts = [
        'precio'    => 'decimal:2',
        'stock'     => 'integer',
        'es_regalo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function carritoItems()
    {
        return $this->hasMany(CarritoItem::class, 'producto_id');
    }
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'producto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'modulo',
        'tienda_id',
        'activo',
        'imagen_url',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDelModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

}
