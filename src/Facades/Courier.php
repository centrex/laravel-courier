<?php

declare(strict_types = 1);

namespace Centrex\Courier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Centrex\Courier\Courier
 */
class Courier extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Centrex\Courier\Courier::class;
    }
}
