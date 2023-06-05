<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Generators;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
use Eyadhamza\LaravelEloquentMigration\Core\Formatters\AddCommandFormatter;
use Eyadhamza\LaravelEloquentMigration\Core\Formatters\MigrationFormatter;
use Eyadhamza\LaravelEloquentMigration\Core\Mappers\ElementToCommandMapper;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class MigrationCommandGenerator extends Generator
{

    private AddCommandFormatter $commandFormatter;

    public function __construct($tableName)
    {
        parent::__construct($tableName);

        $this->commandFormatter = new AddCommandFormatter();
    }

    public function run(Collection $elements, MigrationOperationEnum $operation): self
    {
        return match ($operation) {
            MigrationOperationEnum::Add => $this->runAddedCommand($elements),
            MigrationOperationEnum::Remove => $this->runRemovedCommand($elements),
            MigrationOperationEnum::Modify => $this->runModifiedCommand($elements),
        };
    }
    private function runAddedCommand(Collection $elements): static
    {
        $elements->each(fn($element) => $this->generateCommand($element, MigrationOperationEnum::Add));
        return $this;
    }

    private function runRemovedCommand(Collection $elements): static
    {
        $elements->each(fn($element) => $this->generateCommand($element, MigrationOperationEnum::Remove));
        return $this;
    }

    private function runModifiedCommand(Collection $elements): static
    {
        $elements->each(fn($element) => $this->generateCommand($element, MigrationOperationEnum::Modify));
        return $this;
    }

    public function generateCommand(ElementToCommandMapper $element, MigrationOperationEnum $operation): self
    {
        $formattedCommand = $this->commandFormatter
            ->setNameOrNames($element->getName())
            ->setOperation($operation)
            ->setType($element->getElementType())
            ->setOptions($element->toArray())
            ->format();

        $this->generated->add($formattedCommand);

        return $this;
    }

    private function getRules(Fluent $column): array
    {
        return array_filter($column->getAttributes(), fn($key,) => !in_array($key, ['type', 'name', 'columns']), ARRAY_FILTER_USE_KEY);
    }

}
