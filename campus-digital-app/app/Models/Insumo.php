<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'insumos';
    protected $primaryKey = 'id_insumo';
    const DELETED_AT      = 'deleted_at';

    protected $fillable = [
        'nombre_insumo',
        'stock_actual',
    ];

    protected $casts = [
        'stock_actual' => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];
}
