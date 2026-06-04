<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaldoRegla extends Model
{
    use SoftDeletes;

    protected $table = 'saldo_reglas';

    protected $fillable = [
        'usuario_id',
        'tipo_limite',
        'monto_limite',
        'modulo',
        'activo',
        'descripcion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'monto_limite' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación: Una regla pertenece a un usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Scope: Solo reglas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true)->whereNull('deleted_at');
    }

    /**
     * Scope: Por tipo de límite
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_limite', $tipo);
    }

    /**
     * Scope: Por módulo
     */
    public function scopePorModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    /**
     * Scope: Por usuario
     */
    public function scopePorUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }
}
