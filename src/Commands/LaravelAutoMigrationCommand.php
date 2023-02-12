<?php

namespace Eyadhamza\LaravelAutoMigration\Commands;

use Illuminate\Console\Command;

class LaravelAutoMigrationCommand extends Command
{
    public $signature = 'laravel-auto-migration';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
