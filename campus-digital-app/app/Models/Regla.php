<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Regla (Global)
 * 
 * Representa reglas de negocio globales aplicadas a los catálogos principales.
 * 
 * PRECEDENCIA DE REGLAS:
 * 1. Si existe una ReglaVendedor para un CatalogoVendedor, se aplica PRIMERO (mayor prioridad)
 * 2. Si NO existe ReglaVendedor, se busca la Regla global del Catalogo base
 * 3. Si NO existe ninguna regla, no hay restricción
 * 
 * Esto permite que vendedores personalicen reglas sin afectar el catálogo global.
 * 
 * @property int $id_regla
 * @property int $id_catalogo - Referencia al catálogo principal
 * @property string $tipo_regla - Tipo de regla (ej: 'cantidad_minima', 'horario', etc)
 * @property string $descripcion - Descripción de la regla
 */
class Regla extends Model
{
    protected $table = 'reglas';
    protected $primaryKey = 'id_regla';
    public $timestamps = false;

    protected $fillable = [
        'id_catalogo',
        'descripcion',
        'tipo_regla'
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, 'id_catalogo');
    }
}