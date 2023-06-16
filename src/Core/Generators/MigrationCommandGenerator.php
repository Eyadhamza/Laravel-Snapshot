<?php

namespace PiSpace\LaravelSnapshot\Core\Generators;

use PiSpace\LaravelSnapshot\Core\Constants\MigrationOperationEnum;
use PiSpace\LaravelSnapshot\Core\Formatters\AddCommandFormatter;
use PiSpace\LaravelSnapshot\Core\Formatters\ModifyCommandFormatter;
use PiSpace\LaravelSnapshot\Core\Formatters\RemoveCommandFormatter;
use PiSpace\LaravelSnapshot\Core\Mappers\ElementToCommandMapper;
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
