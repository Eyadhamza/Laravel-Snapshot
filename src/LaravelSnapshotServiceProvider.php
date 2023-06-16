<?php

namespace PiSpace\LaravelSnapshot;

use PiSpace\LaravelSnapshot\Commands\GenerateMigrationCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelSnapshotServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-snapshot')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands([
                GenerateMigrationCommand::class
            ])
            ->hasMigration('create_laravel-auto-migration_table')
            ->hasCommand(GenerateMigrationCommand::class);
    }
}
