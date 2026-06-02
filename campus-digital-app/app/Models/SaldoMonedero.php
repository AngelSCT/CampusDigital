<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaldoMonedero extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'saldo_monedero';

    protected $fillable = [
        'usuario_id',
        'saldo_disponible',
        'saldo_retenido',
    ];

    protected $casts = [
        'saldo_disponible' => 'decimal:2',
        'saldo_retenido'   => 'decimal:2',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function movimientos()
    {
        return $this->hasMany(SaldoMovimiento::class, 'saldo_monedero_id');
    }


    public static function obtenerOCrear(int $usuarioId): self
    {
        return self::firstOrCreate(
            ['usuario_id' => $usuarioId],
            ['saldo_disponible' => 0.00, 'saldo_retenido' => 0.00]
        );
    }

    public function tieneSaldo(float $monto): bool
    {
        return $this->saldo_disponible >= $monto;
    }

    public function abonar(float $monto, string $concepto, string $modulo = 'otro', ?int $operadorId = null): SaldoMovimiento
    {
        return \DB::transaction(function () use ($monto, $concepto, $modulo, $operadorId) {
            $anterior = $this->saldo_disponible;
            $this->saldo_disponible += $monto;
            $this->save();

            return SaldoMovimiento::create([
                'usuario_id'          => $this->usuario_id,
                'saldo_monedero_id'   => $this->id,
                'tipo'                => 'abono',
                'monto'               => $monto,
                'saldo_anterior'      => $anterior,
                'saldo_nuevo'         => $this->saldo_disponible,
                'modulo'              => $modulo,
                'concepto'            => $concepto,
                'operador_usuario_id' => $operadorId,
            ]);
        });
    }

    /**
     * Mueve un monto de saldo_disponible → saldo_retenido (cuenta puente / escrow).
     * El dinero NO se consume: queda bloqueado hasta que el destinatario acepte
     * el regalo (Módulo 4.5) o el comprador cancele en el período de gracia.
     */
    public function retener(float $monto, string $concepto, string $modulo = 'souvenirs', ?int $operadorId = null): SaldoMovimiento
    {
        if (! $this->tieneSaldo($monto)) {
            throw new \Exception('Saldo insuficiente para retener en escrow.');
        }

        return \DB::transaction(function () use ($monto, $concepto, $modulo, $operadorId) {
            $anterior = $this->saldo_disponible;

            // Mover: disponible ↓, retenido ↑ (dinero bloqueado en escrow)
            $this->saldo_disponible -= $monto;
            $this->saldo_retenido   += $monto;
            $this->save();

            return SaldoMovimiento::create([
                'usuario_id'          => $this->usuario_id,
                'saldo_monedero_id'   => $this->id,
                'tipo'                => 'cargo',   // usa el tipo existente en DB
                'monto'               => $monto,
                'saldo_anterior'      => $anterior,
                'saldo_nuevo'         => $this->saldo_disponible,
                'modulo'              => $modulo,
                'concepto'            => "[ESCROW] {$concepto}",
                'operador_usuario_id' => $operadorId,
            ]);
        });
    }

    public function cargar(float $monto, string $concepto, string $modulo = 'otro', ?int $operadorId = null, ?int $tarjetaLecturaId = null): SaldoMovimiento
    {
        if (!$this->tieneSaldo($monto)) {
            throw new \Exception('Saldo insuficiente.');
        }

        return \DB::transaction(function () use ($monto, $concepto, $modulo, $operadorId, $tarjetaLecturaId) {
            $anterior = $this->saldo_disponible;
            $this->saldo_disponible -= $monto;
            $this->save();

            return SaldoMovimiento::create([
                'usuario_id'          => $this->usuario_id,
                'saldo_monedero_id'   => $this->id,
                'tipo'                => 'cargo',
                'monto'               => $monto,
                'saldo_anterior'      => $anterior,
                'saldo_nuevo'         => $this->saldo_disponible,
                'modulo'              => $modulo,
                'concepto'            => $concepto,
                'operador_usuario_id' => $operadorId,
                'tarjeta_lectura_id'  => $tarjetaLecturaId,
            ]);
        });
    }
}