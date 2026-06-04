<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReglaCategoria extends Model
{
    protected $table = 'cart_reglas_categoria';

    protected $fillable = [
        'categoria_id',
        'clave',
        'valor',
        'tipo_dato',
    ];

    // Claves estándar reconocidas por el sistema.
    const CLAVE_CANTIDAD_MAXIMA        = 'cantidad_maxima';
    const CLAVE_REQUIERE_SALDO         = 'requiere_saldo';
    const CLAVE_PERMITE_DEVOLUCION     = 'permite_devolucion';
    const CLAVE_DURACION_MAXIMA_HORAS  = 'duracion_maxima_horas';
    const CLAVE_PERMITE_PAGO_DIFERIDO  = 'permite_pago_diferido';
    const CLAVE_PRECIO_MINIMO          = 'precio_minimo';
    const CLAVE_PERMITE_PRECIO_CERO    = 'permite_precio_cero';

    const TIPO_INT    = 'int';
    const TIPO_BOOL   = 'bool';
    const TIPO_STRING = 'string';
    const TIPO_JSON   = 'json';

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /** Devuelve el valor casteado al tipo_dato declarado. */
    public function valorCasteado(): mixed
    {
        return match ($this->tipo_dato) {
            self::TIPO_INT    => (int) $this->valor,
            self::TIPO_BOOL   => filter_var($this->valor, FILTER_VALIDATE_BOOLEAN),
            self::TIPO_JSON   => json_decode($this->valor, true),
            default           => $this->valor,
        };
    }
}
