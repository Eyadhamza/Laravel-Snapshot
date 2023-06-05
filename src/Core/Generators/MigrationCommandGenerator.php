<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Generators;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
use Eyadhamza\LaravelEloquentMigration\Core\Formatters\AddCommandFormatter;
use Eyadhamza\LaravelEloquentMigration\Core\Formatters\ModifyCommandFormatter;
use Eyadhamza\LaravelEloquentMigration\Core\Formatters\RemoveCommandFormatter;
use Eyadhamza\LaravelEloquentMigration\Core\Mappers\ElementToCommandMapper;
use Illuminate\Support\Collection;

class MigrationCommandGenerator extends Generator
{
    public function __construct($tableName)
    {
        parent::__construct($tableName);
    }

    public function run(Collection $elements, MigrationOperationEnum $operation): self
    {
        $elements->each(function (ElementToCommandMapper $element) use ($operation) {

            $commandFormatter = match ($operation) {
                MigrationOperationEnum::Add => new AddCommandFormatter(),
                MigrationOperationEnum::Remove => new RemoveCommandFormatter(),
                MigrationOperationEnum::Modify => new ModifyCommandFormatter(),
            };

            $formattedCommand = $commandFormatter
                ->setElement($element)
                ->setOperation($operation)
                ->setOptions($element->toArray())
                ->format();

            $this->generated->add($formattedCommand);
        });

        return $this;
    }

}
