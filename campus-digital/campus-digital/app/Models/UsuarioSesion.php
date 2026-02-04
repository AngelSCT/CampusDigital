<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsuarioSesion extends Model
{
    use SoftDeletes;

    protected $table = 'usuario_sesion';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'usuario_id',
        'token_hash',
        'ip',
        'user_agent',
        'dispositivo',
        'creado_por',
        'expira_at',
        'revocado_at',
    ];

    protected $casts = [
        'expira_at' => 'datetime',
        'revocado_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function actividades()
    {
        return $this->hasMany(ActividadBitacora::class, 'sesion_id');
    }
}