<?php

namespace App\Modules\Cart\Exceptions;

/** El módulo Saldo no está disponible, y la situación no puede resolverse con pago diferido (→ 503). */
class SaldoUnavailableException extends \RuntimeException {}
