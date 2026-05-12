<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipoActivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'equipos_activos';
    protected $primaryKey = 'id_equipo';
    const DELETED_AT      = 'deleted_at';

    protected $fillable = [
        'id_categoria',
        'id_ubicacion',
        'nombre_equipo',
        'estado_actual',
    ];

    protected $casts = [
        'id_categoria' => 'integer',
        'id_ubicacion' => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaTicket::class, 'id_categoria', 'id_categoria');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion', 'id_ubicacion');
    }
}
