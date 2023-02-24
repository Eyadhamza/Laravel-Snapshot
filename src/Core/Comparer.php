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
    private Collection $intersectedColumns;
    private Collection $addedIndexes;
    private Collection $removedIndexes;
    private Collection $intersectedIndexes;
    private Collection $addedForeignKeys;
    private Collection $removedForeignKeys;
    private Collection $intersectedForeignKeys;

    public function __construct(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper)
    {

        $this->migrationGenerator = new MigrationGenerator($doctrineMapper->getTableName());
        $this->addedColumns = $modelMapper->getColumns()->diffKeys($doctrineMapper->getColumns());
        $this->removedColumns = $doctrineMapper->getColumns()->diffKeys($modelMapper->getColumns());
        $this->intersectedColumns = $modelMapper->getColumns()->intersectByKeys($doctrineMapper->getColumns());

        $this->addedIndexes = $modelMapper->getIndexes()->diffKeys($doctrineMapper->getIndexes());
        $this->removedIndexes = $doctrineMapper->getIndexes()->diffKeys($modelMapper->getIndexes());
        $this->intersectedIndexes = $modelMapper->getIndexes()->intersectByKeys($doctrineMapper->getIndexes());
        dump([ '$doctrineMapper->getForeignKeys()' => $doctrineMapper->getForeignKeys()]);
        dump([ '$modelMapper->getForeignKeys()' => $modelMapper->getForeignKeys()]);
        $this->addedForeignKeys = $modelMapper->getForeignKeys()->diffKeys($doctrineMapper->getForeignKeys());
        $this->removedForeignKeys = $doctrineMapper->getForeignKeys()->diffKeys($modelMapper->getForeignKeys());
        $this->intersectedForeignKeys = $modelMapper->getForeignKeys()->intersectByKeys($doctrineMapper->getForeignKeys());
        dump([ 'addedForeignKeys' => $this->addedForeignKeys]);
        dump([ 'removedForeignKeys' => $this->removedForeignKeys]);
        dump([ 'intersectedForeignKeys' => $this->intersectedForeignKeys]);
    }

    public static function make(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper): Comparer
    {
        return new self($doctrineMapper, $modelMapper);
    }

    public function getMigrationGenerator(): MigrationGenerator
    {
        $this->compareIntersectedColumns()
            ->addNewColumns()
            ->removeOldColumns()
            ->compareIntersectedIndexes()
            ->addNewIndexes()
            ->removeOldIndexes()
            ->compareIntersectedForeignKeys()
            ->addNewForeignKeys()
            ->removeOldForeignKeys();

        return $this->migrationGenerator;

    }

    private function compareIntersectedColumns(): self
    {
        $this->intersectedColumns = $this->intersectedColumns->map(function (Fluent $column) {
            $modifiedAttributes = $this->getIntersectedAttributes($column);
            if ($modifiedAttributes->isNotEmpty()) {
                return $this->migrationGenerator->generateModifiedCommand($column, $modifiedAttributes);
            }
            return $this;
        });

        return $this;
    }

    private function addNewColumns(): self
    {
        $this->addedColumns = $this->addedColumns->map(function (ColumnDefinition $column) {
            return $this->migrationGenerator->generateAddedCommand($column, $column->get('name'));
        });
        return $this;
    }

    private function removeOldColumns(): self
    {
        $this->removedColumns = $this->removedColumns->map(function (ColumnDefinition $column) {
            return $this->migrationGenerator->generateRemovedCommand($column, 'dropColumn');
        });
        return $this;
    }

    private function compareIntersectedIndexes(): self
    {
        $this->intersectedIndexes = $this->intersectedIndexes->map(function (IndexDefinition $index) {
            $modifiedAttributes = $this->getIntersectedAttributes($index);
            if ($modifiedAttributes->isNotEmpty()) {
                return $this->migrationGenerator->generateModifiedCommand($index, $modifiedAttributes);
            }
            return $this;
        });

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

    private function compareIntersectedForeignKeys(): self
    {
        $this->intersectedForeignKeys = $this->intersectedForeignKeys->map(function (ForeignKeyDefinition $foreignKey) {
            $modifiedAttributes = $this->getIntersectedAttributes($foreignKey);
            if ($modifiedAttributes->isNotEmpty()) {
                return $this->migrationGenerator->generateModifiedCommand($foreignKey, $modifiedAttributes);
            }
            return $this;
        });

        return $this;
    }

    private function addNewForeignKeys(): self
    {
        $this->addedForeignKeys = $this->addedForeignKeys->map(function (ForeignKeyDefinition $foreignKey) {
            return $this->migrationGenerator->generateAddedCommand($foreignKey,$foreignKey->get('columns'));
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

    private function getIntersectedAttributes(Fluent $column): Collection
    {
        return collect($column->getAttributes())->filter(function ($value, $attribute) use ($column) {
            return $value !== $column->get($attribute);
        });
    }
}
