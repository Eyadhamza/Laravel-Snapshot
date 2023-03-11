<?php

namespace Eyadhamza\LaravelEloquentMigration\Commands;

use Illuminate\Console\Command;

class LaravelEloquentMigrationCommand extends Command
{
    public $signature = 'laravel-auto-migration';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
