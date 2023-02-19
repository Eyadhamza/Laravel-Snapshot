<?php

namespace Eyadhamza\LaravelAutoMigration\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Eyadhamza\LaravelAutoMigration\LaravelAutoMigrationServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'Eyadhamza\\LaravelAutoMigration\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelAutoMigrationServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-auto-migration_table.php.stub';
        $migration->up();
        */
    }
}
