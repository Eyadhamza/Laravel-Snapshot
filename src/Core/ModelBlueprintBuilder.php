<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\BlueprintColumnBuilder;
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
        $this->mappedColumns = $this->modelProperties->map(function (BlueprintColumnBuilder $modelProperty) {
            $rules = $modelProperty->getRules();
            $columnType = $modelProperty->getType();
            $columnName = $modelProperty->getName();

            $column = $this->blueprint->$columnType($columnName);
            $mappedColumn = "\$table" . "->$columnType" . "('$columnName')";

            $mappedRules = [];
            foreach ($rules as $rule => $value) {
                if (is_int($rule)) {
                    $mappedRules[] = $rule;
                    $mappedColumn = $mappedColumn . "->{$value}()";
                    continue;
                }

                $column->{$rule}($value);
                $mappedColumn = $mappedColumn . "->{$rule}('$value')";
                $mappedRules[] = [$value => $rule];
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
}
