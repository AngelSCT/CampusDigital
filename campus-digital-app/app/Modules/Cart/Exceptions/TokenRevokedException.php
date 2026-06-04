<?php

namespace App\Modules\Cart\Exceptions;

class TokenRevokedException extends CartTokenException
{
    public function __construct()
    {
        parent::__construct('TOKEN_REVOKED');
    }
}
