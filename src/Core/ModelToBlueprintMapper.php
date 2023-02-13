<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;
use Illuminate\Support\Collection;

class ModelToBlueprintMapper
{
    private Collection $modelProperties;

    private Collection $columns;
    public function __construct(Collection $modelProperties)
    {
        $this->modelProperties = $modelProperties;
    }

    public static function make(Collection $modelProperties): self
    {
        return new self($modelProperties);
    }

    public function build(): self
    {
        $rules = $this->modelProperties->getRules();

        $blueprint = $this->buildColumn($modelProperty, $modelName);
//        $allowedRules = new ReflectionClass(Rule::class)->getConstants()

        foreach ($rules as $rule => $value) {
            if (is_int($rule)) {
                $rule = $value;
                $blueprint->{$rule}();
                return $this;
            }

            if (!array_key_exists($rule, $allowedRules)) {
                throw new \Exception("Name {$rule} not found");
            }

            $blueprint->{$rule}($value);

        }
        return $this;
    }

    private function buildColumns(Property $property, string $modelName)
    {
        $blueprint = $this->modelBlueprints[$modelName];

        $columnType = $this->mapToColumn($property->getPropertyType());

        $columnName = $property->getPropertyName();

        return $blueprint->$columnType($columnName);
    }

    private function mapToColumn(string $propertyType): string
    {
        return match ($propertyType) {
            'int' => 'integer',
            'string' => 'string',
        };
    }

    public function mapRules(): Collection
    {
        return $this->modelProperties->map(function ($property) {
            return $property->getAttributes('Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\After');
        });
    }
}
