<?php

namespace App\Modules\Cart\Exceptions;

class ScopeDeniedException extends CartTokenException
{
    public function __construct()
    {
        parent::__construct('SCOPE_DENIED');
    }
}
