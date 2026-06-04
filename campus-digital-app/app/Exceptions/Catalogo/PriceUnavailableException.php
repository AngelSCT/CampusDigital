<?php

namespace App\Exceptions\Catalogo;

class PriceUnavailableException extends \RuntimeException
{
    public function __construct(string $message = 'El ítem no tiene precio vigente configurado.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
