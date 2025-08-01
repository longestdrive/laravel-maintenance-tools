<?php

namespace Longestdrive\LaravelMaintenanceTools\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Longestdrive\LaravelMaintenanceTools\LaravelMaintenanceTools
 */
class LaravelMaintenanceTools extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Longestdrive\LaravelMaintenanceTools\LaravelMaintenanceTools::class;
    }
}
