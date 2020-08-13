<?php

namespace Gentritabazi01\LarapiComponents\Facades;

use Illuminate\Support\Facades\Facade;

class ApiConsumer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'apiconsumer';
    }
}
