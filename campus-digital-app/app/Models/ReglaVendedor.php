<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ReglaVendedor
 * 
 * Representa reglas de negocio específicas para catálogos personalizados de vendedor.
 * Permite que cada vendedor tenga reglas customizadas en sus ítems sin afectar otros vendedores.
 * 
 * PRECEDENCIA DE REGLAS (Sistema jerárquico):
 * 1. ReglaVendedor (esta tabla) - MÁXIMA PRIORIDAD
 *    → Si existe una regla específica del vendedor, se aplica sin consultar otras
 *    → Permite personalización completa por vendedor
 * 
 * 2. Regla (tabla reglas global) - PRIORIDAD MEDIA
 *    → Se aplica si NO existe ReglaVendedor para ese item
 *    → Proporciona regla "por defecto" del catálogo base
 * 
 * 3. Sin regla - PRIORIDAD BAJA
 *    → Si no existe ni ReglaVendedor ni Regla global, no hay restricción
 * 
 * EJEMPLO DE USO:
 * - Catálogo base "Pizza" tiene Regla: "cantidad_mínima=1"
 * - Vendedor "Pizzería A" crea CatalogoVendedor de "Pizza"
 * - Para "Pizzería A", se aplica su ReglaVendedor si existe, sino la Regla global
 * - Vendedor "Pizzería B" puede tener diferente ReglaVendedor o usar la global
 * 
 * @property int $id_regla_v
 * @property int $id_cv - Referencia al CatalogoVendedor
 * @property string $tipo_regla - Tipo de regla (ej: 'cantidad_minima', 'horario', etc)
 * @property string $descripcion - Descripción específica de la regla del vendedor
 */
class ReglaVendedor extends Model
{
    protected $table = 'reglas_vendedor';
    protected $primaryKey = 'id_regla_v';
    public $timestamps = false;

    protected $fillable = [
        'id_cv',
        'descripcion',
        'tipo_regla',
    ];

    public function catalogoVendedor()
    {
        return $this->belongsTo(CatalogoVendedor::class, 'id_cv');
    }
}
