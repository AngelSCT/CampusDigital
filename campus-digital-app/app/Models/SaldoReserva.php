<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Reserva temporal de saldo (hold) creada durante el checkout del módulo 4.4.
 *
 * Flujo:
 *  1. reservar()  → crea registro en ESTADO_PENDIENTE, mueve fondos a saldo_retenido
 *  2. confirmar() → ejecuta el cargo real, libera saldo_retenido
 *  3. liberar()   → cancela la reserva, devuelve fondos a saldo_disponible
 */
class SaldoReserva extends Model
{
    use HasFactory;

    protected $table = 'saldo_reserva';

    protected $fillable = [
        'uuid',
        'usuario_id',
        'saldo_monedero_id',
        'monto',
        'carrito_uuid',
        'modulo_slug',
        'concepto',
        'estado',
        'expira_at',
        'saldo_movimiento_id',
    ];

    protected $casts = [
        'monto'      => 'decimal:2',
        'expira_at'  => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Estados ──────────────────────────────────────────────────────────────

    const ESTADO_PENDIENTE  = 'pendiente';   // Fondos retenidos, esperando confirmación
    const ESTADO_CONFIRMADA = 'confirmada';  // Cargo ejecutado con éxito
    const ESTADO_LIBERADA   = 'liberada';    // Cancelada, fondos devueltos
    const ESTADO_EXPIRADA   = 'expirada';    // TTL superado sin confirmar

    // TTL en minutos para cada reserva
    const TTL_MINUTOS = 5;

    // ── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $reserva) {
            if (empty($reserva->uuid)) {
                $reserva->uuid = (string) Str::uuid();
            }
            if (empty($reserva->expira_at)) {
                $reserva->expira_at = now()->addMinutes(self::TTL_MINUTOS);
            }
            if (empty($reserva->estado)) {
                $reserva->estado = self::ESTADO_PENDIENTE;
            }
        });
    }

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function monedero()
    {
        return $this->belongsTo(SaldoMonedero::class, 'saldo_monedero_id');
    }

    public function movimiento()
    {
        return $this->belongsTo(SaldoMovimiento::class, 'saldo_movimiento_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function estaVigente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE
            && $this->expira_at->isFuture();
    }

    public function estaExpirada(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE
            && $this->expira_at->isPast();
    }
}
