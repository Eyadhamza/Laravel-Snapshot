<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\BlueprintAttributeEntity;
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
        $this->mappedColumns = $this->modelProperties->map(function (BlueprintAttributeEntity $modelProperty) {
            $rules = $modelProperty->getRules();
            $columnType = $modelProperty->getType();
            $columnName = $modelProperty->getName();

            $column = $this->blueprint->$columnType($columnName);
            $mappedColumn = "\$table" . "->$columnType" . "({$this->getColumnNameOrNames($columnName)})";

            if (!$rules) {
                return $mappedColumn . ";";
            }
            foreach ($rules as $rule => $value) {
                dump($rule, $value);
                if (is_int($rule)) {
                    $column->{$value}();
                    $mappedColumn = $mappedColumn . "->$value()";
                    continue;
                }
                $column->{$rule}($value);
                $mappedColumn = $mappedColumn . "->{$rule}('$value')";
            }
            return $mappedColumn . ";";
        });
        return $this;
    }

    public function build(): self
    {
        $this->buildColumns();
        return $this;
    }

    private function getColumnNameOrNames(mixed $name): string
    {
        if (!$name) {
            return '';
        }
        return is_array($name) ? "['" . implode("','", $name) . "']" : "'{$name}'";
    }

}
