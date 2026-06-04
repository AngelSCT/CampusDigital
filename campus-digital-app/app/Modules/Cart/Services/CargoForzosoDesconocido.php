<?php

namespace App\Modules\Cart\Services;

/**
 * cargoForzoso() recibió HTTP 409/5xx, timeout, excepción o respuesta ambigua.
 *
 * Resultado DESCONOCIDO: el cargo pudo haberse ejecutado o no.
 * NO reintentar automáticamente — escalarlo a requiere_revision_manual.
 */
final class CargoForzosoDesconocido extends SaldoResult {}
