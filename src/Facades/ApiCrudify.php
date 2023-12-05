<?php

namespace Mehedi8gb\ApiCrudify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mehedi8gb\ApiCrudify\ApiCrudify
 */
class ApiCrudify extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mehedi8gb\ApiCrudify\ApiCrudify::class;
    }
}
