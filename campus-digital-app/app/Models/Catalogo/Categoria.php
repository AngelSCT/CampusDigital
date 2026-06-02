<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory;
    protected $table = 'categorias';

    protected $primaryKey = 'id_categoria';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function catalogos(): HasMany
    {
        return $this->hasMany(Catalogo::class, 'id_categoria', 'id_categoria');
    }
}
