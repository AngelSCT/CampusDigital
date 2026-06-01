<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recarga extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recarga';

    protected $fillable = [
        'usuario_id',
        'monto',
        'metodo_pago',
        'estado',
        'referencia_pago',
        'razon_fallo',
        'saldo_movimiento_id',
        'meta_json',
    ];

    protected $casts = [
        'monto'      => 'decimal:2',
        'meta_json'  => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Estados posibles
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EXITOSO   = 'exitoso';
    const ESTADO_FALLIDO   = 'fallido';

    // Métodos de pago aceptados
    const METODOS_PAGO = ['tarjeta', 'transferencia', 'efectivo'];

    // ─── Relaciones ───────────────────────────────────────────

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function saldoMovimiento()
    {
        return $this->belongsTo(SaldoMovimiento::class, 'saldo_movimiento_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function esPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function fueExitosa(): bool
    {
        return $this->estado === self::ESTADO_EXITOSO;
    }

    public function falló(): bool
    {
        return $this->estado === self::ESTADO_FALLIDO;
    }
}
