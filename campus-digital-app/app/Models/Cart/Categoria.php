<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'cart_categorias';

    protected $fillable = [
        'slug',
        'nombre',
        'descripcion',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function reglas(): HasMany
    {
        return $this->hasMany(ReglaCategoria::class, 'categoria_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemCarrito::class, 'categoria_id');
    }
}
