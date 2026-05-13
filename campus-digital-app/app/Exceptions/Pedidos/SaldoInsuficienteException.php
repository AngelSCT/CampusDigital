<?php

namespace App\Exceptions\Pedidos;

use RuntimeException;

class SaldoInsuficienteException extends RuntimeException
{
    public function __construct(
        public readonly float $saldoActual,
        public readonly float $totalRequerido,
    ) {
        parent::__construct(
            sprintf(
                "Saldo insuficiente. Actual: $%.2f, requerido: $%.2f, faltan $%.2f.",
                $saldoActual,
                $totalRequerido,
                $totalRequerido - $saldoActual
            )
        );
    }
}