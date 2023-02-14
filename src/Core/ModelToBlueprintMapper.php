<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class ModelToBlueprintMapper
{

    private Blueprint $blueprint;
    private Collection $modelProperties;

    private Collection $columns;

    public function __construct(Collection $modelProperties, Blueprint $blueprint)
    {
        $this->modelProperties = $modelProperties;
        $this->blueprint = $blueprint;
    }

    public static function make(Collection $modelProperties,Blueprint $blueprint): self
    {
        return new self($modelProperties, $blueprint);
    }

    public function build(): self
    {
        $this->modelProperties->each(function (Property $modelProperty) {
            $this->buildColumn($modelProperty);
        });

        return $this;
    }

    private function buildColumn(Property $property)
    {
        $rules = $property->getRules();

        $columnType = $property->getType();

        $columnName = $property->getName();
        $column = $this->blueprint->$columnType($columnName);
        foreach ($rules as $rule) {
            if (empty($rule->getArguments())) {
                $column->{$rule->getName()}();
                continue;
            }
            foreach ($rule->getArguments() as $value){
                $column->{$rule->getName()}($value);
            }
        }
        return $this;
    }


}
