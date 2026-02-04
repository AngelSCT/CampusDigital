<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permiso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permiso';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'clave',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso', 'permiso_id', 'rol_id')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }
}