<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisponibilidadVendedor extends Model
{
    protected $table = 'disponibilidad_vendedor';
    protected $primaryKey = 'id_disp_v';
    public $timestamps = false;

    protected $fillable = [
        'id_cv',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'disponible',
    ];

    public function catalogoVendedor()
    {
        return $this->belongsTo(CatalogoVendedor::class, 'id_cv');
    }
}
