<?php

namespace PiSpace\LaravelSnapshot\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use PiSpace\LaravelSnapshot\LaravelSnapshotServiceProvider;


class TestCase extends Orchestra
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'PiSpace\\LaravelSnapshot\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelSnapshotServiceProvider::class,
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
