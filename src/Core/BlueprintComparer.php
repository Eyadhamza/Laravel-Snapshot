<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Collection;

class BlueprintComparer
{
    private Blueprint $blueprintOfCurrentTable;
    private Blueprint $newBlueprint;
    private Collection $mappedDiff;
    private Blueprint $diffBlueprint;

    public function __construct(Blueprint $blueprintOfCurrentTable,Blueprint $newBlueprint)
    {
        $this->blueprintOfCurrentTable = $blueprintOfCurrentTable;
        $this->newBlueprint = $newBlueprint;
    }

    public static function make(Blueprint $blueprintOfCurrentTable, Blueprint $newBlueprint): BlueprintComparer
    {
        return new self($blueprintOfCurrentTable, $newBlueprint);
    }

    public function getDiff(): BlueprintComparer
    {
        $this->diffBlueprint = new Blueprint($this->blueprintOfCurrentTable->getTable());

        // Compare the columns
        $currentBlueprintColumns = collect($this->blueprintOfCurrentTable->getColumns());
        $newBlueprintColumns = collect($this->newBlueprint->getColumns());
        $addedColumns = $newBlueprintColumns->diffKeys($currentBlueprintColumns);
        $removedColumns = $currentBlueprintColumns->diffKeys($newBlueprintColumns);

        $this->compareModifiedColumns($currentBlueprintColumns, $newBlueprintColumns);

        $this->addNewColumns($addedColumns);

        $this->removeOldColumns($removedColumns);

        return $this;
//        dd($this->blueprintOfCurrentTable);
//
//        // Compare the indexes
//        $currentIndexes = collect($blueprintOfCurrentTable->getCommands())->filter(function ($command) {
//            return $command->get('name') === 'index';
//        })->keyBy(function ($command) {
//            return $command->get('index');
//        });
//        $newIndexes = collect($newBlueprint->getCommands())->filter(function ($command) {
//            return $command->get('name') === 'index';
//        })->keyBy(function ($command) {
//            return $command->get('index');
//        });
//        $addedIndexes = $newIndexes->diffKeys($currentIndexes);
//        $removedIndexes = $currentIndexes->diffKeys($newIndexes);
//        $modifiedIndexes = collect();
//        $currentIndexes->intersectByKeys($newIndexes)
//            ->each(function ($command, $key) use ($newIndexes, &$modifiedIndexes) {
//                if ($command != $newIndexes->get($key)) {
//                    $modifiedIndexes->put($key, $newIndexes->get($key));
//                }
//            });
//
//        // Add and modify indexes in the diff blueprint
//        $addedIndexes->merge($modifiedIndexes)->each(function ($command) use ($diffBlueprint) {
//            $diffBlueprint->index(
//                $command->get('columns'),
//                $command->get('index'),
//                $command->get('algorithm'),
//                $command->get('unique')
//            );
//        });
//
//        // Remove indexes from the diff blueprint
//        $removedIndexes->each(function ($command) use ($diffBlueprint) {
//            $diffBlueprint->dropIndex($command->get('index'));
//        });
//
//        return $diffBlueprint;
    }

    private function compareModifiedColumns(Collection $currentBlueprintColumns, Collection $newBlueprintColumns): self
    {
        $this->mappedDiff = $currentBlueprintColumns->map(function (ColumnDefinition $currentBlueprintColumn) use ($newBlueprintColumns){

            $matchingNewBlueprintColumns = $newBlueprintColumns->where('name', $currentBlueprintColumn->get('name'));

            if ($matchingNewBlueprintColumns->isNotEmpty()) {
                $matchingNewBlueprintColumn = $matchingNewBlueprintColumns->first();

                $modifiedAttributes = collect($matchingNewBlueprintColumn->getAttributes())->filter(function ($value, $attribute) use ($currentBlueprintColumn) {
                    return $value !== $currentBlueprintColumn->get($attribute);
                });

                if ($modifiedAttributes->isNotEmpty()) {
                    $columnType = $matchingNewBlueprintColumn->get('type');
                    $columnName = $matchingNewBlueprintColumn->get('name');
                    $mappedColumn = "\$table" . "->$columnType" . "('$columnName')";
                    foreach ($modifiedAttributes as $attribute => $value) {
                        if ($attribute === 'type' || $attribute === 'name')
                            continue;
                        $mappedColumn = $mappedColumn . "->{$attribute}()";
                    }
                    return $mappedColumn . "->change();";
                }
            }

        })->filter()->values();

        return $this;
    }

    private function addNewColumns(Collection $addedColumns): static
    {
        $this->mappedDiff->add($addedColumns->map(function (ColumnDefinition $column){
            $columnName = $column->get('name');
            return "\$table->addColumn('$columnName');";
        }));

        return $this;
    }

    private function removeOldColumns(Collection $removedColumns): static
    {
        $this->mappedDiff->add($removedColumns->map(function (ColumnDefinition $column){
            $columnName = $column->get('name');
            return "\$table->dropColumn('$columnName');";
        }));

        return $this;
    }


    public function getBlueprint(): Blueprint
    {
        return $this->diffBlueprint;
    }

    public function getMapped(): Collection
    {
        return $this->mappedDiff->flatten()->filter()->values();
    }

}
