<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rol extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rol';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];





    public function permisos()
{
    return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'permiso_id')
        ->withTimestamps()
        ->wherePivotNull('deleted_at');
}

public function usuarios()
{
    return $this->belongsToMany(Usuario::class, 'usuario_rol', 'rol_id', 'usuario_id')
        ->withTimestamps()
        ->wherePivotNull('deleted_at');
}
}