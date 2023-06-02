<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Generators;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperation;
use Eyadhamza\LaravelEloquentMigration\Core\Formatters\MigrationFormatter;
use Illuminate\Support\Fluent;

class MigrationCommandGenerator extends Generator
{

    public function generateCommand(Fluent $column, $operation): self
    {
        $commandFormatter = MigrationFormatter::make($column)
            ->setOperation($operation)
            ->setRules($this->getRules($column))
            ->run();

        $this->generated->add($commandFormatter);
        return $this;
    }

    public function generateAddedCommand(Fluent $column): self
    {
        return $this->generateCommand($column, MigrationOperation::Add);
    }

    public function generateRemovedCommand(Fluent $column): self
    {
        return $this->generateCommand($column, MigrationOperation::Remove);
    }

    public function generateModifiedCommand(Fluent $column): self
    {
        return $this->generateCommand($column, MigrationOperation::Modify);
    }

    private function getRules(Fluent $column): array
    {
        return array_filter($column->getAttributes(), fn($key,) => !in_array($key, ['type', 'name', 'columns']), ARRAY_FILTER_USE_KEY);
    }
}
