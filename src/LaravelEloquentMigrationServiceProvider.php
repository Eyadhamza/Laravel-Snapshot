<?php

namespace Eyadhamza\LaravelEloquentMigration;

use Eyadhamza\LaravelEloquentMigration\Commands\GenerateMigrationCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelEloquentMigrationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-eloquent-migration')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands([
                GenerateMigrationCommand::class
            ])
            ->hasMigration('create_laravel-auto-migration_table')
            ->hasCommand(GenerateMigrationCommand::class);
    }
}
