<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'categorias_ticket';
    protected $primaryKey = 'id_categoria';
    const DELETED_AT      = 'deleted_at';

    protected $fillable = [
        'id_area',
        'nombre_categoria',
        'tiempo_sla_horas',
    ];

    protected $casts = [
        'id_area'          => 'integer',
        'tiempo_sla_horas' => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }
}
