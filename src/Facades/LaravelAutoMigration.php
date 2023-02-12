<?php

namespace Eyadhamza\LaravelAutoMigration\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Eyadhamza\LaravelAutoMigration\LaravelAutoMigration
 */
class LaravelAutoMigration extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Eyadhamza\LaravelAutoMigration\LaravelAutoMigration::class;
    }
}
