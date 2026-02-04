<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsuarioPerfil extends Model
{
    use SoftDeletes;

    protected $table = 'usuario_perfil';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'usuario_id',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'preferencias_json',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'preferencias_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}