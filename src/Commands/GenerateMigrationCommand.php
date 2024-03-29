<?php

namespace PiSpace\LaravelSnapshot\Commands;

use PiSpace\LaravelSnapshot\Core\MigrationBuilder;
use Illuminate\Console\Command;
use Spatie\ModelInfo\ModelInfo;

class GenerateMigrationCommand extends Command
{
    public $signature = 'generate-migrations {--migrate}';

    public $description = 'generate migration from eloquent models';

    public function handle(): int
    {
        MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));

        if ($this->option('migrate')) {
            $this->call('migrate');
        }
        return self::SUCCESS;
    }
}
