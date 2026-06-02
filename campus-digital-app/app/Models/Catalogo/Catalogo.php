<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Catalogo extends Model
{
    use HasFactory;
    protected $table = 'catalogo';

    protected $primaryKey = 'id_catalogo';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'id_categoria',
        'activo',
        'fecha_creacion',
        'aplica_iva',
        'id_impuesto',
    ];

    protected $casts = [
        'activo'         => 'boolean',
        'aplica_iva'     => 'boolean',
        'fecha_creacion' => 'datetime',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function impuesto(): BelongsTo
    {
        return $this->belongsTo(Impuesto::class, 'id_impuesto', 'id_impuesto');
    }

    public function inventario(): HasOne
    {
        return $this->hasOne(Inventario::class, 'id_catalogo', 'id_catalogo');
    }

    public function precios(): HasMany
    {
        return $this->hasMany(Precio::class, 'id_catalogo', 'id_catalogo');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Scope que filtra precios vigentes en la fecha actual.
     */
    public function scopePrecioVigente($query)
    {
        return $query
            ->whereDate('fecha_inicio', '<=', now())
            ->where(fn ($q) => $q->whereNull('fecha_fin')
                ->orWhereDate('fecha_fin', '>=', now()))
            ->orderByDesc('fecha_inicio');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Retorna el valor (float) del primer precio vigente, o null si no existe.
     */
    public function precioVigenteValor(): ?float
    {
        /** @var \App\Models\Catalogo\Precio|null $precio */
        $precio = $this->precios()
            ->whereDate('fecha_inicio', '<=', now())
            ->where(fn ($q) => $q->whereNull('fecha_fin')
                ->orWhereDate('fecha_fin', '>=', now()))
            ->orderByDesc('fecha_inicio')
            ->first();

        return $precio ? (float) $precio->precio : null;
    }
}
