<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Generators;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
use Eyadhamza\LaravelEloquentMigration\Core\Formatters\MigrationFormatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class MigrationCommandGenerator extends Generator
{

    public function run(Collection $elements, MigrationOperationEnum $operation): self
    {
        match ($operation) {
            MigrationOperationEnum::Add => $elements->each(fn(Fluent $element) => $this->generateAddedCommand($element)),
            MigrationOperationEnum::Remove => $elements->each(fn(Fluent $element) => $this->generateRemovedCommand($element)),
            MigrationOperationEnum::Modify => $elements->each(fn(Fluent $element) => $this->generateModifiedCommand($element)),
        };

        return $this;
    }
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
        return $this->generateCommand($column, MigrationOperationEnum::Add);
    }

    public function generateRemovedCommand(Fluent $column): self
    {
        return $this->generateCommand($column, MigrationOperationEnum::Remove);
    }

    public function generateModifiedCommand(Fluent $column): self
    {
        return $this->generateCommand($column, MigrationOperationEnum::Modify);
    }

    private function getRules(Fluent $column): array
    {
        return array_filter($column->getAttributes(), fn($key,) => !in_array($key, ['type', 'name', 'columns']), ARRAY_FILTER_USE_KEY);
    }

}
