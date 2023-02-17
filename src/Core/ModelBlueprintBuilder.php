<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class ModelBlueprintBuilder extends BlueprintBuilder
{
    private Collection $modelProperties;

    public function __construct(Blueprint $blueprint, Collection $modelProperties)
    {
        parent::__construct($blueprint);
        $this->modelProperties = $modelProperties;
    }

    public static function make(Blueprint $blueprint, Collection $modelProperties): self
    {
        return new self($blueprint, $modelProperties);
    }

    private function buildColumns(): self
    {
        $this->mappedColumns = $this->modelProperties->map(function (Column $modelProperty) {
            $rules = $modelProperty->getRules();
            $columnType = $modelProperty->getType();
            $columnName = $modelProperty->getName();
            $column = $this->blueprint->$columnType($columnName);
            $mappedColumn = "\$table" . "->$columnType" . "('$columnName')";
            if (is_null($rules)) {
                return $this->blueprint;
            }
            foreach ($rules as $rule) {
                if (empty($rule->getArguments())) {

                    $column->{$rule->getName()}();
                    $mappedColumn = $mappedColumn . "->{$rule->getName()}()";
                    continue;
                }
                foreach ($rule->getArguments() as $value) {
                    $column->{$rule->getName()}($value);

                    $mappedColumn = $mappedColumn . "->{$rule->getName()}($value)";
                }
            }
            return $mappedColumn . ";";
        });
        return $this;
    }

    public function build(): self
    {
        return $this->buildColumns();
    }
}
