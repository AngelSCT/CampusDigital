<?php

namespace App\Exceptions\Catalogo;

class CategoryUnavailableException extends \RuntimeException
{
    public function __construct(string $message = 'La categoría del ítem no está disponible para el carrito.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
