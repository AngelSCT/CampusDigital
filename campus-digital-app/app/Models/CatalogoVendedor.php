<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo CatalogoVendedor
 * 
 * Catálogo personalizado de un vendedor. Cada vendedor puede tener su propia versión
 * de productos del catálogo global con precios, disponibilidad y reglas específicas.
 * 
 * RELACIONES Y PRECEDENCIA:
 * - Precio: PrecioVendedor > Precio (global) > sin precio
 * - Disponibilidad: DisponibilidadVendedor > Disponibilidad (global) > siempre disponible
 * - Regla: ReglaVendedor > Regla (global) > sin regla
 */
class CatalogoVendedor extends Model
{
    protected $table = 'catalogo_vendedor';
    protected $primaryKey = 'id_cv';
    public $timestamps = false;

    protected $fillable = [
        'id_vendedor',
        'id_catalogo_base',
        'nombre_personalizado',
        'descripcion_personalizada',
        'tipo',
        'id_categoria',
        'activo',
    ];

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'id_vendedor');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function catalogoBase()
    {
        return $this->belongsTo(Catalogo::class, 'id_catalogo_base', 'id_catalogo');
    }

    /**
     * Obtiene las relaciones con precios, disponibilidad y reglas del vendedor.
     */
    public function preciosVendedor()
    {
        return $this->hasMany(PrecioVendedor::class, 'id_cv');
    }

    public function disponibilidadVendedor()
    {
        return $this->hasMany(DisponibilidadVendedor::class, 'id_cv');
    }

    public function reglasVendedor()
    {
        return $this->hasMany(ReglaVendedor::class, 'id_cv');
    }

    /**
     * Obtiene el precio aplicable con precedencia: Vendedor > Global > null
     * 
     * @return PrecioVendedor|object|null
     */
    public function getPrecioAplicable()
    {
        // 1. Intenta obtener precio de vendedor (vigente)
        $precioVendedor = $this->preciosVendedor()
            ->whereNull('fecha_fin')
            ->first();

        if ($precioVendedor) {
            return $precioVendedor;
        }

        // 2. Fallback: Precio global del catálogo base
        if ($this->catalogoBase) {
            return $this->catalogoBase
                ->precios()
                ->whereNull('fecha_fin')
                ->first();
        }

        return null;
    }

    /**
     * Obtiene la disponibilidad aplicable con precedencia: Vendedor > Global > siempre
     * 
     * @return DisponibilidadVendedor|object|null
     */
    public function getDisponibilidadAplicable()
    {
        // 1. Intenta obtener disponibilidad de vendedor
        $dispVendedor = $this->disponibilidadVendedor()
            ->first();

        if ($dispVendedor) {
            return $dispVendedor;
        }

        // 2. Fallback: Disponibilidad global del catálogo base
        if ($this->catalogoBase) {
            return $this->catalogoBase
                ->disponibilidad()
                ->first();
        }

        return null; // Siempre disponible si no hay restricción
    }

    /**
     * Obtiene la regla aplicable con precedencia: Vendedor > Global > null
     * 
     * IMPORTANTE: Esta es la regla de negocio que se aplica para este ítem específico
     * del vendedor. Vendedor siempre tiene prioridad sobre la regla global del catálogo.
     * 
     * @return ReglaVendedor|Regla|null Retorna la primera regla aplicable según precedencia
     */
    public function getReglaAplicable()
    {
        // 1. Intenta obtener regla del vendedor (MÁXIMA PRIORIDAD)
        $reglaVendedor = $this->reglasVendedor()
            ->first();

        if ($reglaVendedor) {
            return $reglaVendedor;
        }

        // 2. Fallback: Regla global del catálogo base
        if ($this->catalogoBase) {
            return $this->catalogoBase
                ->reglas()
                ->first();
        }

        return null; // Sin regla = sin restricción
    }
}
