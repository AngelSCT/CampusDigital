<?php

namespace App\Modules\Cart\Exceptions;

class TokenReuseDetectedException extends CartTokenException
{
    public function __construct()
    {
        parent::__construct('TOKEN_REUSE_DETECTED');
    }
}
