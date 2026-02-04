<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccesoBitacora extends Model
{
    use SoftDeletes;

    protected $table = 'acceso_bitacora';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'usuario_id',
        'email_intentado',
        'evento',
        'exito',
        'ip',
        'user_agent',
        'detalle',
    ];

    protected $casts = [
        'exito' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}