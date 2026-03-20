<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchivosCarpeta extends Model
{
    use SoftDeletes;

    protected $table = 'archivo_carpeta';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'padre_id',
        'ruta',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function padre()
    {
        return $this->belongsTo(ArchivosCarpeta::class, 'padre_id');
    }

    public function hijos()
    {
        return $this->hasMany(ArchivosCarpeta::class, 'padre_id')->whereNull('deleted_at');
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class, 'carpeta_id')->whereNull('deleted_at');
    }
}