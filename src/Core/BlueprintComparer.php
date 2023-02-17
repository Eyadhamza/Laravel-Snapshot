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

    public function getDiffBlueprint(): Blueprint
    {
        $this->diffBlueprint = new Blueprint($this->blueprintOfCurrentTable->getTable());

        // Compare the columns
        $currentBlueprintColumns = collect($this->blueprintOfCurrentTable->getColumns());
        $newBlueprintColumns = collect($this->newBlueprint->getColumns());
        $addedColumns = $newBlueprintColumns->diffKeys($currentBlueprintColumns);
        $removedColumns = $currentBlueprintColumns->diffKeys($newBlueprintColumns);

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

        dd($this->mappedDiff);
        return $this;
        dd($currentBlueprintColumns->map(function ($currentBlueprintColumn) use ($newBlueprintColumns) {
            $matchingNewBlueprintColumns = $newBlueprintColumns->where('name', $currentBlueprintColumn->get('name'));

            if ($matchingNewBlueprintColumns->isNotEmpty()) {
                $matchingNewBlueprintColumn = $matchingNewBlueprintColumns->first();

                $modifiedAttributes = collect($matchingNewBlueprintColumn->getAttributes())->filter(function ($value, $attribute) use ($currentBlueprintColumn) {
                    return $value !== $currentBlueprintColumn->get($attribute);
                });
                if ($modifiedAttributes->isNotEmpty()) {
                    $columnType = $modifiedAttributes->get('type') ?? $matchingNewBlueprintColumn->get('type');
                    $columnName = $matchingNewBlueprintColumn->get('name');
                    $this->mappedDiff = "\$table->$columnType('$columnName');";
                    $modifiedAttributes->each(function ($value, $attribute){
                        return $this->mappedDiff . "->$attribute()->change();";
                    });
                }
            }
            return $this->mappedDiff;
        })->join());
        // Add and modify columns in the diff blueprint
        $modifiedColumns->each(function (\Illuminate\Support\Collection $columnAttributes) use ($diffBlueprint) {
            $diffBlueprint->$columnAttributes['type']($columnAttributes['name']);
            $columnAttributes->skip();
            dd();
        });
        $addedColumns->merge($modifiedColumns)->each(function (Blueprint $column) use ($diffBlueprint) {
            $column->change();
            $diffBlueprint->addColumn($column);
        });

        // Remove columns from the diff blueprint
        $removedColumns->each(function (Blueprint $column) use ($diffBlueprint) {
            $diffBlueprint->dropColumn($column->getAttributes()['name']);
        });

        // Compare the indexes
        $currentIndexes = collect($blueprintOfCurrentTable->getCommands())->filter(function ($command) {
            return $command->get('name') === 'index';
        })->keyBy(function ($command) {
            return $command->get('index');
        });
        $newIndexes = collect($newBlueprint->getCommands())->filter(function ($command) {
            return $command->get('name') === 'index';
        })->keyBy(function ($command) {
            return $command->get('index');
        });
        $addedIndexes = $newIndexes->diffKeys($currentIndexes);
        $removedIndexes = $currentIndexes->diffKeys($newIndexes);
        $modifiedIndexes = collect();
        $currentIndexes->intersectByKeys($newIndexes)
            ->each(function ($command, $key) use ($newIndexes, &$modifiedIndexes) {
                if ($command != $newIndexes->get($key)) {
                    $modifiedIndexes->put($key, $newIndexes->get($key));
                }
            });

        // Add and modify indexes in the diff blueprint
        $addedIndexes->merge($modifiedIndexes)->each(function ($command) use ($diffBlueprint) {
            $diffBlueprint->index(
                $command->get('columns'),
                $command->get('index'),
                $command->get('algorithm'),
                $command->get('unique')
            );
        });

        // Remove indexes from the diff blueprint
        $removedIndexes->each(function ($command) use ($diffBlueprint) {
            $diffBlueprint->dropIndex($command->get('index'));
        });

        return $diffBlueprint;
    }
}
