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
}
