<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Collection;

class BlueprintComparer
{
    private Collection $mappedDiff;
    private Blueprint $diffBlueprint;
    private Collection $currentBlueprintColumns;
    private Collection $newBlueprintColumns;
    private Collection $addedColumns;
    private Collection $removedColumns;
    private string $table;

    public function __construct(Blueprint $blueprintOfCurrentTable, Blueprint $newBlueprint)
    {
        $this->table = $blueprintOfCurrentTable->getTable();
        $this->diffBlueprint = new Blueprint($this->table);
        $this->currentBlueprintColumns = collect($blueprintOfCurrentTable->getColumns());
        $this->newBlueprintColumns = collect($newBlueprint->getColumns());
        $this->addedColumns = $this->newBlueprintColumns->diffKeys($this->currentBlueprintColumns);
        $this->removedColumns = $this->currentBlueprintColumns->diffKeys($this->newBlueprintColumns);
    }

    public static function make(Blueprint $blueprintOfCurrentTable, Blueprint $newBlueprint): BlueprintComparer
    {
        return new self($blueprintOfCurrentTable, $newBlueprint);
    }

    public function getDiff(): BlueprintComparer
    {
        $this->compareModifiedColumns()
            ->addNewColumns()
            ->removeOldColumns()
            ->compareModifiedIndexes()
            ->addNewIndexes()
            ->removeOldIndexes();

        return $this;

    }

    private function compareModifiedColumns(): self
    {
        $this->mappedDiff = $this->currentBlueprintColumns->map(function (ColumnDefinition $currentBlueprintColumn) {

            $matchingNewBlueprintColumns = $this->getMatchingNewBlueprintColumns($currentBlueprintColumn);
            if ($matchingNewBlueprintColumns->isNotEmpty()) {
                $matchingNewBlueprintColumn = $matchingNewBlueprintColumns->first();

                $modifiedAttributes = $this->getModifiedAttributes($currentBlueprintColumn, $matchingNewBlueprintColumn);
                if ($modifiedAttributes->isNotEmpty()) {
                    return $this->buildMappedColumn($matchingNewBlueprintColumn, $modifiedAttributes);
                }
            }
            return "";
        })->filter()->values();

        return $this;
    }

    private function addNewColumns(): self
    {

        $this->mappedDiff->add($this->addedColumns->map(function (ColumnDefinition $column) {
            $columnName = $column->get('name');
            $columnType = $column->get('type');
            return "\$table->$columnType('$columnName');";
        }));

        return $this;
    }

    private function removeOldColumns(): self
    {
        $this->mappedDiff->add($this->removedColumns->map(function (ColumnDefinition $column) {
            $columnName = $column->get('name');
            return "\$table->dropColumn('$columnName');";
        }));

        return $this;
    }

    public function getDiffBlueprint(): Blueprint
    {
        return $this->diffBlueprint;
    }

    public function getMapped(): Collection
    {
        return $this->mappedDiff->flatten()->filter()->values();
    }

    private function compareModifiedIndexes()
    {
        //                    dd($matchingNewBlueprintColumn);
        //                    $mappedColumn = "\$table->dropForeign(['$columnName']);" . "\n \t \t \t";
        //                    return $mappedColumn . "\$table->foreign('$columnName')->references()->$attribute('$value');" . "\n \t \t \t";
        return $this;
    }

    private function addNewIndexes()
    {
        // TODO: Implement addNewIndexes() method.
        return $this;
    }

    private function removeOldIndexes()
    {
        // TODO: Implement removeOldIndexes() method.
        return $this;
    }

    private function getMatchingNewBlueprintColumns(ColumnDefinition $currentBlueprintColumn): Collection
    {
        return $this->newBlueprintColumns->where('name', $currentBlueprintColumn->get('name'));
    }

    private function getModifiedAttributes(ColumnDefinition $currentBlueprintColumn, $matchingNewBlueprintColumn): Collection
    {
        return collect($matchingNewBlueprintColumn->getAttributes())->filter(function ($value, $attribute) use ($currentBlueprintColumn) {
            return $value !== $currentBlueprintColumn->get($attribute);
        });
    }

    private function buildMappedColumn(ColumnDefinition $matchingNewBlueprintColumn, Collection $modifiedAttributes): string
    {
        $columnType = $matchingNewBlueprintColumn->get('type');
        $columnName = $matchingNewBlueprintColumn->get('name');

        $mappedColumn = "\$table" . "->$columnType" . "('$columnName')";
        return collect($modifiedAttributes)
            ->reject(fn($value, $attribute) => $this->attributesToBeSkipped($attribute))
            ->reject(fn($value, $attribute) => $this->noChangeHappened($attribute))
            ->map(function ($value, $attribute) use ($columnName, $columnType, $mappedColumn, $matchingNewBlueprintColumn) {
                if ($this->attributesAsSecondArgument($attribute)) {
                    return $value ? "\$table->{$columnType}('$columnName', $value)" . "->change();" : "";
                }
                if ($this->inForeignRules($attribute)) {
                    return "";
                }
                return $mappedColumn . "->{$attribute}()" . "->change();";
            })->implode('');
    }

    private function attributesAsSecondArgument($attribute): bool
    {

        return in_array($attribute, ['length', 'precision', 'scale']);
    }

    private function attributesToBeSkipped(int|string|null $attribute): bool
    {
        return in_array($attribute, ['name', 'type']);
    }

    private function noChangeHappened($attribute): bool
    {
        return in_array($attribute, ['autoIncrement']);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    private function inForeignRules($rule): bool
    {
        return in_array($rule, ['cascadeOnDelete', 'cascadeOnUpdate']);
    }


}
