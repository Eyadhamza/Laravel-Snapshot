<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Illuminate\Database\Schema\Blueprint;

class BlueprintComparer
{
    private Blueprint $blueprintOfCurrentTable;
    private Blueprint $newBlueprint;
    public function __construct(Blueprint $blueprintOfCurrentTable,Blueprint $newBlueprint)
    {
        $this->blueprintOfCurrentTable = $blueprintOfCurrentTable;
        $this->newBlueprint = $newBlueprint;
    }

    public static function make(Blueprint $blueprintOfCurrentTable, Blueprint $newBlueprint)
    {
        return new self($blueprintOfCurrentTable, $newBlueprint);
    }

    public function getDiffBlueprint(): Blueprint
    {
        $diffBlueprint = new Blueprint($this->blueprintOfCurrentTable->getTable());

        // Compare the columns
        $currentColumns = collect($this->blueprintOfCurrentTable->getColumns());
        $newColumns = collect($this->newBlueprint->getColumns());
        $addedColumns = $newColumns->diffKeys($currentColumns);
        $removedColumns = $currentColumns->diffKeys($newColumns);
        $modifiedColumns = collect();
        $currentColumns->intersectByKeys($newColumns)
            ->each(function (Blueprint $column, $key) use ($newColumns, &$modifiedColumns) {
                if (!$column->isEqualTo($newColumns->get($key))) {
                    $modifiedColumns->put($key, $newColumns->get($key));
                }
            });

        // Add and modify columns in the diff blueprint
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
