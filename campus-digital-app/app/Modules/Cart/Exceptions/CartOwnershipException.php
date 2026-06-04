<?php

namespace App\Modules\Cart\Exceptions;

/** El carrito no pertenece al módulo autenticado (→ 403, sección 7.4). */
class CartOwnershipException extends \RuntimeException {}
