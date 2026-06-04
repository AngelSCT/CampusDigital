<?php

namespace App\Modules\Cart\Exceptions;

class ModuleInactiveException extends CartTokenException
{
    public function __construct()
    {
        parent::__construct('MODULE_INACTIVE');
    }
}
