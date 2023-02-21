<?php

namespace Eyadhamza\LaravelAutoMigration;

use Eyadhamza\LaravelAutoMigration\Console\Commands\AutoMigrateResetCommand;
use Eyadhamza\LaravelAutoMigration\Console\Commands\AutoMigrateRunCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Eyadhamza\LaravelAutoMigration\Commands\LaravelAutoMigrationCommand;

class LaravelAutoMigrationServiceProvider extends PackageServiceProvider
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
            ->hasCommand(LaravelAutoMigrationCommand::class);
    }
}
