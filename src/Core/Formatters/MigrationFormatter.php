<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Formatters;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
use Illuminate\Support\Fluent;

class MigrationFormatter
{
    private Fluent $column;
    private string|array|null $columnName;
    private MigrationOperationEnum $operation;
    private array $rules;

    public function __construct(Fluent $column)
    {
        $this->column = $column;
        $this->columnName = $column->get('columns') ?? $column->get('name');
    }

    public static function make(Fluent $column): MigrationFormatter
    {
        return new self($column);
    }

    public function run(): string|null
    {
        return match ($this->operation) {
            MigrationOperationEnum::Add => $this->generateAddCommand(),
            MigrationOperationEnum::Remove => $this->generateRemoveCommand(),
            MigrationOperationEnum::Modify => $this->generateModifyCommand(),
        };
    }

    private function getGeneratedTypeAndNameWithOperation(): string
    {
        $columnType = $this->column->get('type') ?? 'index';

        if ($this->operation === MigrationOperationEnum::Remove) {
            $columnType = $this->generateDropCommand($columnType);
        }

        return "\$table" . "->$columnType" . "({$this->getColumnNameOrNames()})";
    }

    private function getColumnNameOrNames(): string
    {
        if (is_string($this->columnName)) {
            return "'$this->columnName'";
        }
        if (is_array($this->columnName)) {
            return count($this->columnName) === 1
                ? "'" . $this->columnName[0] . "'"
                : "['" . implode("','", $this->columnName) . "']";
        }

        return "";
    }

    private function addRule(string $rule, int|string $value = null): string
    {
        if ($this->inForeignRules($rule)) {
            return '';
        }

        if ($this->attributesAsSecondArgument($rule)) {
            return "";
        }

        $methodParameters = is_int($value) ? "" : "'$value'";

        return "->$rule($methodParameters)";
    }

    public function setOperation(MigrationOperationEnum $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function setRules(array $rules): self
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

    private function generateAddCommand(): string
    {
        $generatedCommand = $this->getGeneratedTypeAndNameWithOperation();

        if (empty($this->rules)) {
            return $generatedCommand . ";";
        }
        $generatedCommand = $generatedCommand . collect($this->rules)
                ->map(fn($value, $rule) => $this->addRule($rule, $value))
                ->join('');


        return $generatedCommand . ";";
    }

    private function generateRemoveCommand(): string
    {
        return $this->getGeneratedTypeAndNameWithOperation() . ";";
    }

    private function generateModifyCommand(): string|null
    {
        $generatedCommand = $this->getGeneratedTypeAndNameWithOperation();

        if (empty($this->column->get('changes'))) {
            return null;
        }
        $generatedCommand = $generatedCommand . collect($this->rules)
                ->map(fn($value, $rule) => $this->addRule($rule, $value))
                ->join('');

        return $generatedCommand . "->change();";
    }

    private function generateDropCommand(string|\Closure $columnType): \Closure|string
    {
        return match ($columnType) {
            'index' => 'dropIndex',
            'foreign' => 'dropForeign',
            'unique' => 'dropUnique',
            'primary' => 'dropPrimary',
            default => 'dropColumn',
        };
    }

}
