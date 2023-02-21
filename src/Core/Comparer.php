<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
use Illuminate\Database\Schema\ColumnDefinition;
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
        $this->modifiedColumns = $modelMapper->getColumns()->intersectByKeys($doctrineMapper->getColumns());

        $this->addedIndexes = $modelMapper->getIndexes()->diffKeys($doctrineMapper->getIndexes());
        $this->removedIndexes = $doctrineMapper->getIndexes()->diffKeys($modelMapper->getIndexes());
        $this->modifiedIndexes = $modelMapper->getIndexes()->intersectByKeys($doctrineMapper->getIndexes());

        $this->addedForeignKeys = $modelMapper->getForeignKeys()->diffKeys($doctrineMapper->getForeignKeys());
        $this->removedForeignKeys = $doctrineMapper->getForeignKeys()->diffKeys($modelMapper->getForeignKeys());
        $this->modifiedForeignKeys = $modelMapper->getForeignKeys()->intersectByKeys($doctrineMapper->getForeignKeys());

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
        $this->modifiedColumns = $this->modifiedColumns->map(function (Fluent $column) {
            $modifiedAttributes = $this->getModifiedAttributes($column);
            if ($modifiedAttributes->isNotEmpty()) {
                return $this->migrationGenerator->modifyColumn($column, $modifiedAttributes);
            }
            return $this;
        });

        return $this;
    }

    private function addNewColumns(): self
    {
        $this->addedColumns = $this->addedColumns->map(function (Fluent $column) {
            return  $this->migrationGenerator->addColumn($column);
        });
        return $this;
    }

    private function removeOldColumns(): self
    {
        $this->removedColumns = $this->removedColumns->map(function (ColumnDefinition $column) {
            return $this->migrationGenerator->removeColumn($column);
        });
        return $this;
    }

    private function compareModifiedIndexes(): self
    {
        $this->modifiedIndexes = $this->modifiedIndexes->map(function (Fluent $index) {
            $modifiedAttributes = $this->getModifiedAttributes($index);
            if ($modifiedAttributes->isNotEmpty()) {
                return $this->migrationGenerator->buildIndex($index, $modifiedAttributes);
            }
            return $this;
        });

        return $this;
    }

    private function addNewIndexes(): self
    {
        $this->addedIndexes = $this->addedIndexes->map(function (Fluent|IndexMapper $index) {
            return $this->migrationGenerator->addIndex($index);
        });
        return $this;
    }

    private function removeOldIndexes(): self
    {
        $this->removedIndexes = $this->removedIndexes->map(function (Fluent $index) {
            return $this->migrationGenerator->removeIndex($index);
        });
        return $this;
    }


    private function compareModifiedForeignKeys(): self
    {
        $this->modifiedForeignKeys = $this->modifiedForeignKeys->map(function (Fluent $foreignKey) {
            $modifiedAttributes = $this->getModifiedAttributes($foreignKey);
            if ($modifiedAttributes->isNotEmpty()) {
                return $this->migrationGenerator->buildForeignKey($foreignKey, $modifiedAttributes);
            }
            return $this;
        });

        return $this;
    }

    private function addNewForeignKeys(): self
    {
        $this->addedForeignKeys = $this->addedForeignKeys->map(function (Fluent $foreignKey) {
            return $this->migrationGenerator->addForeignKey($foreignKey);
        });
        return $this;
    }

    private function removeOldForeignKeys(): self
    {
        $this->removedForeignKeys = $this->removedForeignKeys->map(function (Fluent $foreignKey) {
            return $this->migrationGenerator->removeForeignKey($foreignKey);
        });
        return $this;
    }

    private function getModifiedAttributes(Fluent $column): Collection
    {
        return collect($column->getAttributes())->filter(function ($value, $attribute) use ($column) {
            return $value !== $column->get($attribute);
        });
    }
}
