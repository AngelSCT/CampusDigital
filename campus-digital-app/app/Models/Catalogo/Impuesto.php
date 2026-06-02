<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Impuesto extends Model
{
    protected $table = 'impuestos';

    protected $primaryKey = 'id_impuesto';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'porcentaje',
        'activo',
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
        'activo'     => 'boolean',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function catalogos(): HasMany
    {
        return $this->hasMany(Catalogo::class, 'id_impuesto', 'id_impuesto');
    }
}
