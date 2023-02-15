<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class MapToBlueprint
{
    private Blueprint $blueprint;
    private Collection $modelProperties;
    private Collection $mappedColumns;

    public function __construct(Collection $modelProperties, Blueprint $blueprint)
    {
        $this->modelProperties = $modelProperties;
        $this->blueprint = $blueprint;
    }

    public static function make(Collection $modelProperties, Blueprint $blueprint): MapToBlueprint
    {
        $mapper = new self($modelProperties, $blueprint);
        return $mapper->buildColumns();
    }

    private function buildColumns(): MapToBlueprint
    {
        $this->mappedColumns = $this->modelProperties->map(function (Column $modelProperty) {
            $rules = $modelProperty->getRules();
            $columnType = $modelProperty->getType();
            $columnName = $modelProperty->getName();
            $column = $this->blueprint->$columnType($columnName);
            $mappedColumn = "\$table" . "->$columnType" . "('$columnName')";
            if (is_null($rules)){
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

    public function getMappedColumns()
    {
        return $this->mappedColumns;
    }

    public function getBlueprint()
    {
        return $this->blueprint;
    }

}
