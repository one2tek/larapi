<?php

namespace one2tek\larapi\Exceptions;

use Exception;

class LarapiException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
