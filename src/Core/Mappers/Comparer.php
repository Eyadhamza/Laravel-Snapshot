<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers;

use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\IndexMapper;
use Eyadhamza\LaravelEloquentMigration\Core\Generators\MigrationCommandGenerator;
use Eyadhamza\LaravelEloquentMigration\Core\Generators\MigrationGenerator;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class Comparer
{
    private MigrationCommandGenerator $migrationCommandGenerator;
    private DoctrineMapper $doctrineMapper;
    private ModelMapper $modelMapper;
    private string $tableName;

    public function __construct(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper)
    {
        $this->tableName = $doctrineMapper->getTableName();
        $this->migrationCommandGenerator = new MigrationCommandGenerator($doctrineMapper->getTableName());
        $this->doctrineMapper = $doctrineMapper;
        $this->modelMapper = $modelMapper;
    }

    public static function make(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper): Comparer
    {
        return new self($doctrineMapper, $modelMapper);
    }

    public function getMigrationGenerator(): MigrationGenerator
    {
        $this
            ->modifyExistingColumns()
            ->addNewColumns()
            ->removeOldColumns()
            ->addNewIndexes()
            ->removeOldIndexes()
            ->compareModifiedIndexes();


        return MigrationGenerator::make($this->tableName)
            ->setGeneratedCommands($this->migrationCommandGenerator->getGenerated());
    }

    private function modifyExistingColumns(): self
    {
        $modifiedColumns = $this->getModifiedColumns($this->modelMapper->getColumns(), $this->doctrineMapper->getColumns());

        $modifiedColumns->map(function (ColumnDefinition|ForeignKeyDefinition $column) {
            return $this->migrationCommandGenerator->generateModifiedCommand($column);
        });
        return $this;
    }

    private function addNewColumns(): self
    {
        $addedColumns = $this->modelMapper->getColumns()->diffKeys($this->doctrineMapper->getColumns());

        $addedColumns->map(function (ColumnDefinition|ForeignKeyDefinition|IndexDefinition $column) {
            return $this->migrationCommandGenerator->generateAddedCommand($column);
        });
        return $this;
    }

    private function removeOldColumns(): self
    {
        $removedColumns = $this->doctrineMapper->getColumns()->diffKeys($this->modelMapper->getColumns());

        $removedColumns->map(function (ColumnDefinition|ForeignKeyDefinition|IndexDefinition $column) {
            return $this->migrationCommandGenerator->generateRemovedCommand($column);
        });
        return $this;
    }

    private function compareModifiedIndexes(): self
    {
        // TODO: Implement compareModifiedIndexes() method.
        return $this;
    }

    private function addNewIndexes(): self
    {
        $addedIndexes = $this->modelMapper->getIndexes()->diffKeys($this->doctrineMapper->getIndexes());

        $addedIndexes->map(function (Fluent|IndexMapper $index) {
            return $this->migrationGenerator->generateAddedCommand($index);
        });
        return $this;
    }

    private function removeOldIndexes(): self
    {
        $removedIndexes = $this->doctrineMapper->getIndexes()->diffKeys($this->modelMapper->getIndexes());

        $removedIndexes->map(function (IndexDefinition $index) {
            return $this->migrationGenerator->generateRemovedCommand($index);
        });
        return $this;
    }
    private function getModifiedColumns(Collection $modelColumns, Collection $doctrineColumns): Collection
    {
        $intersectedColumns = $modelColumns->intersectByKeys($doctrineColumns);
        $diff = new Collection();
        foreach ($intersectedColumns as $key => $column) {
            if ($column->get('type') == 'foreignId') {
                continue;
            }
            $modelAttributes = $column->getAttributes();
            $doctrineAttributes = $doctrineColumns->get($key)->getAttributes();
            $addedAttributes = array_diff_key($modelAttributes, $doctrineAttributes);
            $changedAttributesFromModel = array_diff_assoc($modelAttributes, $doctrineAttributes);
            $changedAttributesFromDoctrine = array_diff_assoc($doctrineAttributes, $modelAttributes);
            $deletedAttributes = array_diff_key($doctrineAttributes, $modelAttributes);

            $diff->put($key, new ColumnDefinition(array_merge(
                $addedAttributes,
                $changedAttributesFromModel,
                $changedAttributesFromDoctrine,
                $deletedAttributes, [
                'change' => true,
            ])));

        }
        return $diff;
    }

}
