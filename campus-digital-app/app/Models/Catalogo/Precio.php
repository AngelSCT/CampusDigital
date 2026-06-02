<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Precio extends Model
{
    protected $table = 'precios';

    protected $primaryKey = 'id_precio';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_catalogo',
        'precio',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'precio'       => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function catalogo(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'id_catalogo', 'id_catalogo');
    }
}
