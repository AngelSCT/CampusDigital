<?php

namespace App\Modules\Cart\Exceptions;

class TokenExpiredException extends CartTokenException
{
    public function __construct()
    {
        parent::__construct('TOKEN_EXPIRED');
    }
}
