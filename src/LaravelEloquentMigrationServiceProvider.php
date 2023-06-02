<?php

namespace Eyadhamza\LaravelEloquentMigration;

use Eyadhamza\LaravelEloquentMigration\Commands\AutoMigrateResetCommand;
use Eyadhamza\LaravelEloquentMigration\Commands\AutoMigrateRunCommand;
use Eyadhamza\LaravelEloquentMigration\Commands\LaravelEloquentMigrationCommand;
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
            ->name('laravel-auto-migration')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands([
                AutoMigrateResetCommand::class,
                AutoMigrateRunCommand::class,
            ])
            ->hasMigration('create_laravel-auto-migration_table')
            ->hasCommand(LaravelEloquentMigrationCommand::class);
    }
}
