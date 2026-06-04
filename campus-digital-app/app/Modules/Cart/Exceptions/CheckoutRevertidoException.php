<?php

namespace App\Modules\Cart\Exceptions;

/**
 * Se lanza cuando el checkout fue procesado (Carrito + Pedido en BD) pero
 * la confirmación del cobro de Saldo falló post-commit, forzando una
 * compensación completa (Pedido cancelado + saldo liberado + carrito revertido).
 *
 * Unifica los dos modos de fallo post-commit:
 *   - confirmar() devuelve false (409 en Saldo)
 *   - confirmar() lanza excepción (error de red / timeout)
 *
 * → CheckoutController la captura y devuelve HTTP 409 con error='CHECKOUT_REVERTIDO'.
 */
class CheckoutRevertidoException extends \RuntimeException {}
