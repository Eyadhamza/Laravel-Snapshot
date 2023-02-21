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

    public function __construct(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper){
        $this->migrationGenerator = new MigrationGenerator;

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
        $this->modifiedColumns->map(function (ColumnDefinition $column) {
                $modifiedAttributes = $this->getModifiedAttributes($column);
                if ($modifiedAttributes->isNotEmpty()) {
                    return $this->migrationGenerator->buildMappedColumn($column, $modifiedAttributes);
                }
            return $this;
        });

        return $this;
    }

    private function addNewColumns(): self
    {
        $this->addedColumns->map(function (ColumnDefinition $column) {
            return $this->migrationGenerator->addColumn($column);
        });
        return $this;
    }

    private function removeOldColumns(): self
    {
        $this->removedColumns->map(function (ColumnDefinition $column) {
            return $this->migrationGenerator->removeColumn($column);
        });
        return $this;
    }
    private function compareModifiedIndexes(): self
    {

        $this->modifiedIndexes->each(function (Fluent $index) {
                $modifiedAttributes = $this->getModifiedAttributes($index);
                if ($modifiedAttributes->isNotEmpty()) {
                    return $this->migrationGenerator->buildMappedIndex($index, $modifiedAttributes);
                }

            return $this;
        });

        return $this;
    }

    private function addNewIndexes()
    {

        $this->addedIndexes->map(function (Fluent $index) {
            $indexNames = $this->getIndexColumns($index);
            return $this->migrationGenerator->addIndex($indexNames);
        });

        return $this;
    }

    private function removeOldIndexes(): self
    {
        $this->removedIndexes->map(function (Fluent $index) {
            $indexNames = $this->getIndexColumns($index);
            return $this->migrationGenerator->removeIndex($indexNames);
        });
        return $this;
    }

    public function getMigrationGenerator(): MigrationGenerator
    {
        return $this->migrationGenerator;
    }

    private function compareModifiedForeignKeys(): self
    {
        $this->modifiedForeignKeys->map(function (Fluent $foreignKey) {
            $modifiedAttributes = $this->getModifiedAttributes($foreignKey);
            if ($modifiedAttributes->isNotEmpty()) {
                return $this->migrationGenerator->buildMappedForeignKey($foreignKey, $modifiedAttributes);
            }
            return $this;
        });

        return $this;
    }

    private function addNewForeignKeys(): self
    {
        $this->addedForeignKeys->map(function (Fluent $foreignKey) {
            return $this->migrationGenerator->addForeignKey($foreignKey);
        });
        return $this;
    }

    private function removeOldForeignKeys(): self
    {
        $this->removedForeignKeys->map(function (Fluent $foreignKey) {
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


    private function getIndexColumns(Fluent $matchedIndex): string
    {
        return "['" . implode("','", $matchedIndex->get('columns')) . "']";
    }
}
