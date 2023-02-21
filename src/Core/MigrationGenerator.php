<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class MigrationGenerator
{
    public static function make()
    {
        return new self();
    }

//    private function buildMappedColumn(ColumnDefinition $matchingNewBlueprintColumn, Collection $modifiedAttributes): string
//    {
//        $columnType = $matchingNewBlueprintColumn->get('type');
//        $columnName = $matchingNewBlueprintColumn->get('name');
//
//        $mappedColumn = "\$table" . "->$columnType" . "('$columnName')";
//        return collect($modifiedAttributes)
//            ->reject(fn($value, $attribute) => $this->attributesToBeSkipped($attribute))
//            ->reject(fn($value, $attribute) => $this->noChangeHappened($attribute))
//            ->map(function ($value, $attribute) use ($columnName, $columnType, $mappedColumn, $matchingNewBlueprintColumn) {
//                if ($this->attributesAsSecondArgument($attribute)) {
//                    return $value ? "\$table->{$columnType}('$columnName', $value)" . "->change();" : "";
//                }
//                if ($this->inForeignRules($attribute)) {
//                    return "";
//                }
//                return $mappedColumn . "->{$attribute}()" . "->change();";
//            })->implode('');
//    }
    public function handle()
    {
        //    private function generateMigrationCode(AttributeEntity|Column $column)
//    {
//
//        $mappedColumn = "\$table" . "->$columnType" . "({$this->getColumnNameOrNames($columnName)})";
//
//        if (!$rules) {
//            return $mappedColumn . ";";
//        }
//        foreach ($rules as $rule => $value) {
//            if ($this->inForeignRules($value)) {
//                $mappedColumn = $mappedColumn . "->{$value}('$rule')";
//                continue;
//            }
//            if (is_int($rule)) {
//                $mappedColumn = $mappedColumn . "->$value()";
//                continue;
//            }
//            $mappedColumn = $mappedColumn . "->{$rule}('$value')";
//        }
//        return $mappedColumn . ";";
//
//    }
    }




//    private function buildMappedIndex(Fluent $matchedIndex, Collection $modifiedAttributes)
//    {
//        $indexType = $matchedIndex->get('name');
//        $indexColumns = $this->getIndexColumns($matchedIndex);
//        $mappedIndex = "\$table" . "->$indexType" . "($indexColumns)";
//        return collect($modifiedAttributes)->filter(function ($value, $attribute) use ($matchedIndex) {
//            return $value !== $matchedIndex->get($attribute);
//        })->map(function ($value, $attribute) use ($indexType, $mappedIndex) {
//            return $mappedIndex . "->{$attribute}()" . "->change();";
//        })->implode('');
//    }
    public function buildMappedIndex($matchedIndex, Collection $modifiedAttributes)
    {
        
    }

    public function addIndex(string $indexNames)
    {
        
    }

    public function removeIndex(string $indexNames)
    {
    }

    public function buildMappedForeignKey(Fluent $foreignKey, Collection $modifiedAttributes)
    {
        
    }

    public function addForeignKey(Fluent $foreignKey)
    {
    }

    public function removeForeignKey(Fluent $foreignKey)
    {
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

    public function removeColumn($column): string
    {
        $columnName = $column->get('name');
        return "\$table->dropColumn('$columnName');";
    }


    public function getMapped(): Collection
    {
        return $this->mappedDiff->flatten()->filter()->values();
    }

}
