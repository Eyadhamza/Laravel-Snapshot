<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class MigrationGenerator
{
    private Collection $generated;

    public function addColumn($column): self
    {
        $columnType = $column->get('type');
        $columnName = $column->get('name');
        $rules = $column->get('rules');

        $mappedColumn = "\$table" . "->$columnType" . "({$this->getColumnNameOrNames($columnName)})";

        if (!$rules) {
            $this->generated->add($mappedColumn . ";");
            return $this;
        }
        foreach ($rules as $rule => $value) {
            if ($this->inForeignRules($value)) {
                $mappedColumn = $mappedColumn . "->{$value}('$rule')";
                continue;
            }
            if (is_int($rule)) {
                $mappedColumn = $mappedColumn . "->$value()";
                continue;
            }
            $mappedColumn = $mappedColumn . "->{$rule}('$value');";
        }
        $this->generated->add($mappedColumn);
        return $this;
    }

    public function modifyColumn(ColumnDefinition $column, Collection $attributes): self
    {
        $columnType = $column->get('type');
        $columnName = $column->get('name');

        $mappedColumn = "\$table" . "->$columnType" . "('$columnName')";
        collect($attributes)
            ->reject(fn($value, $attribute) => $this->attributesToBeSkipped($attribute))
            ->reject(fn($value, $attribute) => $this->noChangeHappened($attribute))
            ->map(function ($value, $attribute) use ($columnName, $columnType, $mappedColumn, $column) {
                if ($this->attributesAsSecondArgument($attribute)) {
                    return $value ? "\$table->{$columnType}('$columnName', $value)" . "->change();" : "";
                }
                if ($this->inForeignRules($attribute)) {
                    return "";
                }
                return $mappedColumn . "->{$attribute}()" . "->change();";
            });
        $this->generated->add($mappedColumn);
        return $this;
    }

    public function removeColumn($column): self
    {
        $columnName = $column->get('name');
        $this->generated->add("\$table->dropColumn('$columnName');");
        return $this;
    }

    public function buildIndex(Fluent $matchedIndex, Collection $modifiedAttributes): self
    {
        $indexType = $matchedIndex->get('name');
        $indexColumns = $this->getIndexColumns($matchedIndex);
        $mappedIndex = "\$table" . "->$indexType" . "($indexColumns)";
        collect($modifiedAttributes)->filter(function ($value, $attribute) use ($matchedIndex) {
            return $value !== $matchedIndex->get($attribute);
        })->map(function ($value, $attribute) use ($indexType, $mappedIndex) {
            return $mappedIndex . "->{$attribute}()" . "->change();";
        });
        $this->generated->add($mappedIndex);
        return $this;
    }

    public function addIndex(Fluent $index): self
    {
        $indexNames = $this->getIndexColumns($index);
        $this->generated->add("\$table->index($indexNames);");
        return $this;
    }

    public function removeIndex(Fluent $index): self
    {
        $indexNames = $this->getIndexColumns($index);
        $this->generated->add("\$table->dropIndex($indexNames);");

        return $this;
    }

    public function buildForeignKey(Fluent $foreignKey, Collection $modifiedAttributes): self
    {
        $foreignKeyColumns = $this->getIndexColumns($foreignKey);
        $mappedForeignKey = "\$table" . "->foreign" . "($foreignKeyColumns)";
         collect($modifiedAttributes)->filter(function ($value, $attribute) use ($foreignKey) {
            return $value !== $foreignKey->get($attribute);
        })->map(function ($value, $attribute) use ($mappedForeignKey) {
            return $mappedForeignKey . "->{$attribute}()" . "->change();";
        });
        $this->generated->add($mappedForeignKey);
        return $this;
    }

    public function addForeignKey(Fluent $foreignKey): static
    {
        $foreignKeyColumns = $this->getIndexColumns($foreignKey);
        foreach ($foreignKey->get('rules') as $rule => $value) {
            if ($this->inForeignRules($value)) {
                $foreignKeyColumns = $foreignKeyColumns . "->{$value}('$rule')";
                continue;
            }
            $foreignKeyColumns = $foreignKeyColumns . "->{$rule}('$value');";
        }
        $this->generated->add($foreignKeyColumns);
        return $this;
    }

    public function removeForeignKey(Fluent $foreignKey): self
    {
        $foreignKeyColumns = $this->getIndexColumns($foreignKey);
        $this->generated->add("\$table->dropForeign($foreignKeyColumns);");
        return $this;
    }

    public function getGenerated()
    {
        return $this->generated->flatten()->filter()->values();
    }

    private function attributesAsSecondArgument($attribute): bool
    {
        return in_array($attribute, ['length', 'precision', 'scale']);
    }

    private function inForeignRules($rule): bool
    {
        return in_array($rule, ['cascadeOnDelete', 'cascadeOnUpdate']);
    }

    private function attributesToBeSkipped(int|string|null $attribute): bool
    {
        return in_array($attribute, ['name', 'type']);
    }

    private function noChangeHappened($attribute): bool
    {
        return in_array($attribute, ['autoIncrement']);
    }


    private function getIndexColumns(Fluent $matchedIndex): string
    {
        return "['" . implode("','", $matchedIndex->get('columns')) . "']";
    }

    private function getColumnNameOrNames(mixed $columnName): string
    {
        return is_array($columnName) ? "['" . implode("','", $columnName) . "']" : "'$columnName'";
    }

}
