<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class ModelToBlueprintMapper
{

    private Blueprint $blueprint;
    private Collection $modelProperties;

    public function __construct(Collection $modelProperties, Blueprint $blueprint)
    {
        $this->modelProperties = $modelProperties;
        $this->blueprint = $blueprint;
    }

    public static function make(Collection $modelProperties,Blueprint $blueprint): self
    {
        return new self($modelProperties, $blueprint);
    }

    public function ToBlueprint(): Blueprint
    {
        return $this->buildColumns();

    }

    private function buildColumns(): Blueprint
    {
        $this->modelProperties->each(function (Property $modelProperty) {
            $rules = $modelProperty->getRules();
            $columnType = $modelProperty->getType();
            $columnName = $modelProperty->getName();

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
        });
        return $this->blueprint;
    }
    public function getBlueprint(): Blueprint
    {
        return $this->blueprint;
    }

    public function getModelProperties(): Collection
    {
        return $this->modelProperties;
    }


}
