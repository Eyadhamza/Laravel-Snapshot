<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class MapToBlueprint
{
    private Blueprint $blueprint;
    private Collection $modelProperties;

    public function __construct(Collection $modelProperties, Blueprint $blueprint)
    {
        $this->modelProperties = $modelProperties;
        $this->blueprint = $blueprint;
    }

    public static function make(Collection $modelProperties, Blueprint $blueprint): Blueprint
    {
        $mapper = new self($modelProperties, $blueprint);
        return $mapper->buildColumns();
    }

    private function buildColumns(): Blueprint
    {
        $this->modelProperties->each(function (Column $modelProperty) {
            $rules = $modelProperty->getRules();
            $columnType = $modelProperty->getType();
            $columnName = $modelProperty->getName();

            $column = $this->blueprint->$columnType($columnName);
            foreach ($rules as $rule) {
                if (empty($rule->getArguments())) {
                    $column->{$rule->getName()}();
                    continue;
                }
                foreach ($rule->getArguments() as $value) {
                    $column->{$rule->getName()}($value);
                }
            }
        });
        return $this->blueprint;
    }

}
