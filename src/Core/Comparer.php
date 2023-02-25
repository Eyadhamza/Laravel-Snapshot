<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class Comparer
{
    private MigrationGenerator $migrationGenerator;
    private Collection $addedColumns;
    private Collection $removedColumns;
    private Collection $modifiedColumns;
    private Collection $addedIndexes;
    private Collection $removedIndexes;
    private Collection $modifiedIndexes;
    private Collection $addedForeignKeys;
    private Collection $removedForeignKeys;
    private Collection $modifiedForeignKeys;

    public function __construct(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper)
    {

        $this->migrationGenerator = new MigrationGenerator($doctrineMapper->getTableName());
        $this->addedColumns = $modelMapper->getColumns()->diffKeys($doctrineMapper->getColumns());
        $this->removedColumns = $doctrineMapper->getColumns()->diffKeys($modelMapper->getColumns());
        $this->modifiedColumns = $this->getModifiedAttributes($modelMapper->getColumns(), $doctrineMapper->getColumns());
        $this->addedIndexes = $modelMapper->getIndexes()->diffKeys($doctrineMapper->getIndexes());
        $this->removedIndexes = $doctrineMapper->getIndexes()->diffKeys($modelMapper->getIndexes());
        $this->modifiedIndexes = $this->getModifiedAttributes($modelMapper->getIndexes(), $doctrineMapper->getIndexes());
        $this->addedForeignKeys = $modelMapper->getForeignKeys()->diffKeys($doctrineMapper->getForeignKeys());
        $this->removedForeignKeys = $doctrineMapper->getForeignKeys()->diffKeys($modelMapper->getForeignKeys());
        $this->modifiedForeignKeys = $this->getModifiedAttributes($modelMapper->getForeignKeys(), $doctrineMapper->getForeignKeys());
    }

    public static function make(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper): Comparer
    {
        return new self($doctrineMapper, $modelMapper);
    }

    public function getMigrationGenerator(): MigrationGenerator
    {
        $this->compareModifiedColumns()
            ->addNewColumns()
            ->removeOldColumns()
            ->compareModifiedIndexes()
            ->addNewIndexes()
            ->removeOldIndexes()
            ->compareModifiedForeignKeys()
            ->addNewForeignKeys()
            ->removeOldForeignKeys();

        return $this->migrationGenerator;

    }

    private function compareModifiedColumns(): self
    {
        // TODO: Implement compareModifiedColumns() method.
        return $this;
    }

    private function addNewColumns(): self
    {
        $this->addedColumns = $this->addedColumns->map(function (ColumnDefinition|ForeignKeyDefinition $column) {
            return $this->migrationGenerator->generateAddedCommand($column, $column->get('name'));
        });
        return $this;
    }

    private function removeOldColumns(): self
    {
        $this->removedColumns = $this->removedColumns->map(function (ColumnDefinition|ForeignKeyDefinition $column) {
            return $this->migrationGenerator->generateRemovedCommand($column, 'dropColumn');
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
        $this->addedIndexes = $this->addedIndexes->map(function (Fluent|IndexMapper $index) {
            return $this->migrationGenerator->generateAddedCommand($index, $index->get('columns'));
        });
        return $this;
    }

    private function removeOldIndexes(): self
    {
        $this->removedIndexes = $this->removedIndexes->map(function (IndexDefinition $index) {
            return $this->migrationGenerator->generateRemovedCommand($index, 'dropIndex');
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
        $this->addedForeignKeys = $this->addedForeignKeys->map(function (ForeignKeyDefinition $foreignKey) {
            return $this->migrationGenerator->generateAddedCommand($foreignKey, $foreignKey->get('columns'));
        });
        return $this;
    }

    private function removeOldForeignKeys(): self
    {
        $this->removedForeignKeys = $this->removedForeignKeys->map(function (ForeignKeyDefinition $foreignKey) {
            return $this->migrationGenerator->generateRemovedCommand($foreignKey, 'dropForeign');
        });
        return $this;
    }


    private function getModifiedAttributes(Collection $modelColumns, Collection $doctrineColumns): Collection
    {
        $intersectedColumns = $modelColumns->intersectByKeys($doctrineColumns);
        $diff = new Collection();
        foreach ($intersectedColumns as $key => $column) {
            $modelAttributes = $column->getAttributes();
            $doctrineAttributes = $doctrineColumns->get($key)->getAttributes();
            $modifiedAttributes = array_diff_assoc($modelAttributes, $doctrineAttributes);
            if ($modifiedAttributes > 0) {
                $diff->put($key, $modifiedAttributes);
            }
        }
        return $diff;
    }

    private function isAllowedAttribute(string $key): bool
    {
        return !in_array($key, ['name', 'type']);
    }


}
