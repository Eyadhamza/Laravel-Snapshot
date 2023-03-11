<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\Index;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class Comparer
{
    private MigrationGenerator $migrationGenerator;
    private DoctrineMapper $doctrineMapper;
    private ModelMapper $modelMapper;

    public function __construct(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper)
    {
        $this->migrationGenerator = new MigrationGenerator($doctrineMapper->getTableName());
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
        return $this->migrationGenerator;

    }

    private function modifyExistingColumns(): self
    {
        $modifiedColumns = $this->getModifiedColumns($this->modelMapper->getColumns(), $this->doctrineMapper->getColumns());

        $modifiedColumns->map(function (ColumnDefinition|ForeignKeyDefinition $column) {
            return $this->migrationGenerator->generateModifiedCommand($column);
        });
        return $this;
    }

    private function addNewColumns(): self
    {
        $addedColumns = $this->modelMapper->getColumns()->diffKeys($this->doctrineMapper->getColumns());

        $addedColumns->map(function (ColumnDefinition|ForeignKeyDefinition|IndexDefinition $column) {
            return $this->migrationGenerator->generateAddedCommand($column);
        });
        return $this;
    }

    private function removeOldColumns(): self
    {
        $removedColumns = $this->doctrineMapper->getColumns()->diffKeys($this->modelMapper->getColumns());

        $removedColumns->map(function (ColumnDefinition|ForeignKeyDefinition|IndexDefinition $column) {
            return $this->migrationGenerator->generateRemovedCommand($column);
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

    private function compareModifiedForeignKeys(): self
    {
        // TODO: Implement compareModifiedForeignKeys() method.
        return $this;
    }

    private function addNewForeignKeys(): self
    {
        $addedForeignKeys = $this->modelMapper->getForeignKeys()->diffKeys($this->doctrineMapper->getForeignKeys());

        $addedForeignKeys->map(function (ForeignKeyDefinition $foreignKey) {
            return $this->migrationGenerator->generateAddedCommand($foreignKey);
        });
        return $this;
    }

    private function removeOldForeignKeys(): self
    {
        $removedForeignKeys = $this->doctrineMapper->getForeignKeys()->diffKeys($this->modelMapper->getForeignKeys());

        $removedForeignKeys->map(function (ForeignKeyDefinition $foreignKey) {
            return $this->migrationGenerator->generateRemovedCommand($foreignKey, 'dropForeign');
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
