<?php

namespace App\Modules\Cart\Exceptions;

class TokenInvalidException extends CartTokenException
{
    public function __construct()
    {
        parent::__construct('TOKEN_INVALID');
    }
}
