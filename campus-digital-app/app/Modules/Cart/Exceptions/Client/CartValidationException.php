<?php

namespace App\Modules\Cart\Exceptions\Client;

class CartValidationException extends \RuntimeException
{
    public function __construct(
        string                  $message,
        public readonly array   $errors = [],
        int                     $code   = 0,
        ?\Throwable             $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
