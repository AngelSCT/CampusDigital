<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo de referencia — la tabla 'area' y su gestión
 * pertenecen al módulo externo de Areas.
 * Este archivo solo existe para que Eloquent pueda
 * resolver la relación en CategoriaTicket.
 */
class Area extends Model
{
    use SoftDeletes;

    protected $table = 'area';

    protected $primaryKey = 'id_area';

    // Sin $fillable: este módulo no escribe en esta tabla.
    protected $guarded = ['*'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
