<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'area';
    protected $primaryKey = 'id_area';
    const DELETED_AT      = 'deleted_at';

    protected $fillable = [
        'name_area',
        'nombre',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'nombre',
    ];

    public function getNombreAttribute()
    {
        return $this->attributes['name_area'] ?? null;
    }

    public function setNombreAttribute($value)
    {
        $this->attributes['name_area'] = $value;
    }
}
