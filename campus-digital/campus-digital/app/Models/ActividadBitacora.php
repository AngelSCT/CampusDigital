<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActividadBitacora extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'actividad_bitacora';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'usuario_id',
        'sesion_id',
        'accion',
        'modulo',
        'target_tabla',
        'target_id',
        'exito',
        'detalle',
        'ip',
        'user_agent',
        'meta_json',
    ];

    protected $casts = [
        'exito' => 'boolean',
        'meta_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function sesion()
    {
        return $this->belongsTo(UsuarioSesion::class, 'sesion_id');
    }
}