<?php

namespace App\Modules\Cart\Exceptions;

/** Operación inválida por estado terminal o expiración del carrito (→ 409). */
class CartStateException extends \RuntimeException {}
