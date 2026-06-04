<?php

namespace App\Modules\Cart\Exceptions;

/** El módulo Saldo confirmó que el usuario no tiene fondos suficientes (→ 402). */
class SaldoInsufficientFundsException extends \RuntimeException {}
