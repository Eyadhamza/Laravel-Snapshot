<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;

class DoctrineBlueprintBuilder extends BlueprintBuilder
{
    private Table $doctrineTableDetails;

    public function __construct(Blueprint $blueprint)
    {
        parent::__construct($blueprint);

        $this->doctrineTableDetails = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->introspectTable($blueprint->getTable());;
    }

    public static function make(Blueprint $blueprint): self
    {
        return new self($blueprint);
    }

    public function buildColumns(): static
    {
        $attributes = [];
        collect($this->doctrineTableDetails->getColumns())->map(function (Column $column) {
            $attributes = collect([
                'name' => $column->getName(),
                'unsigned' => $column->getUnsigned(),
                'nullable' => $column->getNotnull() == false,
                'default' => $column->getDefault(),
                'length' => $column->getLength(),
                'precision' => $column->getPrecision(),
                'scale' => $column->getScale(),
            ])->filter()->toArray();
            $attributes['type'] = match ($column->getType()->getName()) {
                'datetime' => 'timestamp',
                default => $column->getType()->getName(),
            };
            return $this->blueprint->addColumn($attributes['type'], $attributes['name'], $attributes);
        });
        return $this;
    }

    public function buildIndexes(): static
    {
        collect($this->doctrineTableDetails->getIndexes())->map(function (Index $index) {
            $indexDefinition = $this->blueprint->index($index->getColumns(), $index->getName());

            if ($index->isPrimary()) {
                $indexDefinition->primary();
            }
            if ($index->isUnique()) {
                $indexDefinition->unique();
            }
            return $indexDefinition;
        });
        return $this;
    }

    public function buildForeignKeys(): static
    {
        collect($this->doctrineTableDetails->getForeignKeys())
            ->map(function (ForeignKeyConstraint $foreignKey) {
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

                return $foreignKeyDefinition;
            });
        return $this;
    }

    public function buildNew(): self
    {
        return $this->buildColumns()
            ->buildForeignKeys()
            ->buildIndexes();
    }
}
