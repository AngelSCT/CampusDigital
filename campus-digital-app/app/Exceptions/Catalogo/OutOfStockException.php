<?php

namespace App\Exceptions\Catalogo;

class OutOfStockException extends \RuntimeException
{
    public function __construct(string $message = 'El ítem no tiene stock suficiente para la cantidad solicitada.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
