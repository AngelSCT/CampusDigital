<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MantenimientoPreventivo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table      = 'mantenimientos_preventivos';
    protected $primaryKey = 'id_preventivo';
    const DELETED_AT      = 'deleted_at';

    protected $fillable = [
        'id_equipo',
        'proxima_fecha_programada',
    ];

    protected $casts = [
        'id_equipo'               => 'integer',
        'proxima_fecha_programada' => 'date',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
        'deleted_at'              => 'datetime',
    ];

    public function equipo()
    {
        return $this->belongsTo(EquipoActivo::class, 'id_equipo', 'id_equipo');
    }
}
