<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Formatters;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
use Eyadhamza\LaravelEloquentMigration\Core\Mappers\ElementToCommandMapper;
use Illuminate\Support\Fluent;

class MigrationFormatter
{
    private ElementToCommandMapper $element;
    private string|array|null $elementName;
    private MigrationOperationEnum $operation;
    private array $rules;
    private AddCommandFormatter $commandFormattter;

    public function __construct(ElementToCommandMapper $element)
    {
        $this->element = $element;
        $this->commandFormattter = new AddCommandFormatter();

        $this->elementName = $element->get('elements') ?? $element->get('name');
    }

    public static function make(ElementToCommandMapper $element): MigrationFormatter
    {
        return new self($element);
    }

    public function run(): string|null
    {
        $formatter =  match ($this->operation) {
            MigrationOperationEnum::Add => new AddCommandFormatter(),
            MigrationOperationEnum::Remove => new RemoveCommandFormatter(),
            MigrationOperationEnum::Modify => new ModifyCommandFormatter(),
        };

        return $formatter
            ->setNameOrNames($this->elementName)
            ->setOperation($this->operation)
            ->setType($this->element->getElementType())
            ->setOptions($this->rules)
            ->format();
    }


    public function setOptions(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    private function attributesAsSecondArgument($attribute): bool
    {
        return in_array($attribute, ['length', 'precision', 'scale']);
    }

    private function inForeignRules($rule): bool
    {
        return in_array($rule, ['cascadeOnDelete', 'cascadeOnUpdate']);
    }
//
//    private function formatModifyCommand(): string|null
//    {
//        $formatdCommand = $this->getGeneratedTypeAndNameWithOperation();
//
//        if (empty($this->element->get('changes'))) {
//            return null;
//        }
//        $formatdCommand = $formatdCommand . collect($this->rules)
//                ->map(fn($value, $rule) => $this->addRule($rule, $value))
//                ->join('');
//
//        return $formatdCommand . "->change();";
//    }
//
//    private function formatDropCommand(string|\Closure $elementType): \Closure|string
//    {
//        return match ($elementType) {
//            'index' => 'dropIndex',
//            'foreign' => 'dropForeign',
//            'unique' => 'dropUnique',
//            'primary' => 'dropPrimary',
//            default => 'dropColumn',
//        };
//    }

}
