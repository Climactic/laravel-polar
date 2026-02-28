<?php

namespace Climactic\LaravelPolar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Climactic\LaravelPolar\LaravelPolar
 */
class LaravelPolar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Climactic\LaravelPolar\LaravelPolar::class;
    }
}
