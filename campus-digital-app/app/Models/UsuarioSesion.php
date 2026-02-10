<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsuarioSesion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'usuario_sesion';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'usuario_id',
        'session_id',
        'ip',
        'user_agent',
        'inicia_at',
        'expira_at',
        'termina_at',
        'activa',
        'meta_json',
    ];

    protected $casts = [
        'inicia_at' => 'datetime',
        'expira_at' => 'datetime',
        'termina_at' => 'datetime',
        'activa' => 'boolean',
        'meta_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function accesos()
    {
        return $this->hasMany(AccesoBitacora::class, 'sesion_id');
    }

    public function actividades()
    {
        return $this->hasMany(ActividadBitacora::class, 'sesion_id');
    }
}