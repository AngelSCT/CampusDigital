<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventario extends Model
{
    use HasFactory;
    protected $table = 'inventario';

    protected $primaryKey = 'id_inventario';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_catalogo',
        'stock_actual',
        'stock_minimo',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'fecha_actualizacion' => 'datetime',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function catalogo(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'id_catalogo', 'id_catalogo');
    }
}
