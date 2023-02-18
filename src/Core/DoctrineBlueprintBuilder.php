<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DoctrineBlueprintBuilder extends BlueprintBuilder
{
    private Table $doctrineTableDetails;

    public function __construct(Blueprint $blueprint)
    {
        parent::__construct($blueprint);

        $this->doctrineTableDetails = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableDetails($blueprint->getTable());;
    }

    public static function make(Blueprint $blueprint): self
    {
        return new self($blueprint);
    }

    public function buildColumns(): static
    {
        foreach ($this->doctrineTableDetails->getColumns() as $column) {
            $columnDefinition = $this->blueprint->{$column->getType()->getName()}($column->getName());
//            dd($column, $columnDefinition);
            $columnDefinition->nullable(!$column->getNotnull());
            if ($column->getUnsigned()) {
                $columnDefinition->unsigned();
            }
            if ($column->getAutoincrement()) {
                $columnDefinition->autoIncrement();
            }
            if ($column->getLength() !== null) {
                $columnDefinition->length($column->getLength());
            }
            if ($column->getDefault() !== null) {
                $columnDefinition->default($column->getDefault());
            }
        }
        return $this;
    }

    public function buildIndexes(): static
    {
        foreach ($this->doctrineTableDetails->getIndexes() as $index) {
            $indexDefinition = $this->blueprint->index($index->getColumns(), $index->getName());
            if ($index->isPrimary()) {
                $indexDefinition->primary();
            }
            if ($index->isUnique()) {
                $indexDefinition->unique();
            }
        }
        return $this;
    }

    public function buildForeignKeys(): static
    {
        foreach ($this->doctrineTableDetails->getForeignKeys() as $foreignKey) {
            $foreignKeyDefinition = $this->blueprint->foreign($foreignKey->getLocalColumns())
                ->references($foreignKey->getForeignColumns())
                ->on($foreignKey->getForeignTableName())
                ->name($foreignKey->getName());
            if ($foreignKey->getOption('onUpdate') !== null) {
                $foreignKeyDefinition->onUpdate($foreignKey->getOption('onUpdate'));
            }
            if ($foreignKey->getOption('onDelete') !== null) {
                $foreignKeyDefinition->onDelete($foreignKey->getOption('onDelete'));
            }
        }
        return $this;
    }

    public function build(): self
    {
        return $this->buildColumns()
            ->buildForeignKeys()
            ->buildIndexes();
    }
}
