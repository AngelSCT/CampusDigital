<?php

namespace App\Modules\Cart\Services;

/** Saldo confirmó los fondos y creó una reserva. */
final class SaldoConfirmed extends SaldoResult
{
    public function __construct(public readonly ?string $reservaId = null) {}
}
