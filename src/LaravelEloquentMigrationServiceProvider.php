<?php

namespace Eyadhamza\LaravelEloquentMigration;

use Eyadhamza\LaravelEloquentMigration\Console\Commands\AutoMigrateResetCommand;
use Eyadhamza\LaravelEloquentMigration\Console\Commands\AutoMigrateRunCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Eyadhamza\LaravelEloquentMigration\Commands\LaravelEloquentMigrationCommand;

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
