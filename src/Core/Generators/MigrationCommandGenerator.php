<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Generators;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
use Eyadhamza\LaravelEloquentMigration\Core\Formatters\MigrationFormatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class MigrationCommandGenerator extends Generator
{

    // I need to accept a certain format of data, we need to wrap the "to be created columns in an object TODO"
    public function run(Collection $elements): self
    {
        dump($elements);
        $elements
            ->each(fn($operations, $element) => collect($operations)
                ->each(fn($operands, $operation) => $this->generateCommand($element, MigrationOperationEnum::from($operation), $operands)));

        return $this;
    }

    public function generateCommand(string $columnName, MigrationOperationEnum $operation, array $operands): self
    {
        if (empty($operands)) {
            return $this;
        }
        dd($columnName, $operation);
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
