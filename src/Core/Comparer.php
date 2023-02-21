<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class Comparer
{
    private Collection $addedColumns;
    private Collection $removedColumns;
    private Collection $modifiedColumns;
    private Collection $addedIndexes;
    private Collection $removedIndexes;
    private Collection $modifiedIndexes;
    private Collection $addedForeignKeys;
    private Collection $removedForeignKeys;
    private Collection $modifiedForeignKeys;

    public function __construct(private DoctrineMapper $doctrineMapper, private ModelMapper $modelMapper){
        $this->addedColumns = $this->modelMapper->getColumns()->diffKeys($this->doctrineMapper->getColumns());
        $this->removedColumns = $this->doctrineMapper->getColumns()->diffKeys($this->modelMapper->getColumns());
        $this->modifiedColumns = $this->modelMapper->getColumns()->intersectByKeys($this->doctrineMapper->getColumns());

        $this->addedIndexes = $this->modelMapper->getIndexes()->diffKeys($this->doctrineMapper->getIndexes());
        $this->removedIndexes = $this->doctrineMapper->getIndexes()->diffKeys($this->modelMapper->getIndexes());
        $this->modifiedIndexes = $this->modelMapper->getIndexes()->intersectByKeys($this->doctrineMapper->getIndexes());

        $this->addedForeignKeys = $this->modelMapper->getForeignKeys()->diffKeys($this->doctrineMapper->getForeignKeys());
        $this->removedForeignKeys = $this->doctrineMapper->getForeignKeys()->diffKeys($this->modelMapper->getForeignKeys());
        $this->modifiedForeignKeys = $this->modelMapper->getForeignKeys()->intersectByKeys($this->doctrineMapper->getForeignKeys());

    }

    public static function make(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper): Comparer
    {
        return new self($doctrineMapper, $modelMapper);
    }

    public function getDiff(): Comparer
    {
        $this->compareModifiedColumns()
            ->addNewColumns()
            ->removeOldColumns()
            ->compareModifiedIndexes()
            ->addNewIndexes()
            ->removeOldIndexes();
        dd($this->mappedDiff);
        return $this;

    }

    private function compareModifiedColumns(): self
    {
        $this->modifiedColumns->map(function (ColumnDefinition $currentBlueprintColumn) {

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
        $addedColumns = $this->modelColumns->diffKeys($this->doctrineColumns);

        $this->mappedDiff->add($addedColumns->map(function (ColumnDefinition $column) {
            $columnName = $column->get('name');
            $columnType = $column->get('type');
            return "\$table->$columnType('$columnName');";
        }));

        return $this;
    }

    private function removeOldColumns(): self
    {
        $removedColumns = $this->doctrineMapper->getColumns()->diffKeys($this->modelMapper->getColumns());

        $this->mappedDiff->add($removedColumns->map(function (ColumnDefinition $column) {
            $columnName = $column->get('name');
            return "\$table->dropColumn('$columnName');";
        }));

        return $this;
    }
    public function getMapped(): Collection
    {
        return $this->mappedDiff->flatten()->filter()->values();
    }

    private function compareModifiedIndexes(): self
    {
        $this->doctrineIndexes->each(function (Fluent $doctrineIndex) {
            $matchedIndexes = $this->modelIndexes->where('index', $doctrineIndex->get('index'));
            if ($matchedIndexes->isNotEmpty()) {
                $matchedIndex = $matchedIndexes->first();

                $modifiedAttributes = $this->getModifiedAttributes($matchedIndex, $doctrineIndex);
                if ($modifiedAttributes->isNotEmpty()) {
                    return $this->buildMappedIndex($matchedIndex, $modifiedAttributes);
                }
            }
            return "";
        });

        return $this;
    }

    private function addNewIndexes()
    {
        $addedIndexes = $this->modelIndexes->diffKeys($this->doctrineIndexes);

        $this->mappedDiff->add($addedIndexes->map(function (Fluent $index) {
            $indexNames = $this->getIndexColumns($index);

            return "\$table->index($indexNames);";
        }));

        return $this;
    }

    private function removeOldIndexes(): self
    {
        $removedIndexes = $this->doctrineIndexes->diffKeys($this->modelIndexes);
        $this->mappedDiff->add($removedIndexes->map(function (Fluent $index) {
            $indexNames = $this->getIndexColumns($index);
            return "\$table->dropIndex($indexNames);";
        }));

        return $this;
    }

    private function getMatchingNewBlueprintColumns(ColumnDefinition $currentBlueprintColumn): Collection
    {
        return $this->modelColumns->where('name', $currentBlueprintColumn->get('name'));
    }

    private function getModifiedAttributes(Fluent $currentBlueprintColumn, Fluent $matchingNewBlueprintColumn): Collection
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

    private function buildMappedIndex(Fluent $matchedIndex, Collection $modifiedAttributes)
    {
        $indexType = $matchedIndex->get('name');
        $indexColumns = $this->getIndexColumns($matchedIndex);
        $mappedIndex = "\$table" . "->$indexType" . "($indexColumns)";
        return collect($modifiedAttributes)->filter(function ($value, $attribute) use ($matchedIndex) {
            return $value !== $matchedIndex->get($attribute);
        })->map(function ($value, $attribute) use ($indexType, $mappedIndex) {
            return $mappedIndex . "->{$attribute}()" . "->change();";
        })->implode('');
    }

    private function getIndexColumns(Fluent $matchedIndex): string
    {
        return "['" . implode("','", $matchedIndex->get('columns')) . "']";
    }
}
