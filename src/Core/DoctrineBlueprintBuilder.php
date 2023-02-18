<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Schema\Blueprint;
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

        collect($this->doctrineTableDetails->getColumns())->map(function (Column $column){
            $columnDefinition = $this->blueprint->{$column->getType()->getName()}($column->getName());
            match (true) {
                $column->getAutoincrement() => $columnDefinition->autoIncrement(),
                $column->getUnsigned() => $columnDefinition->unsigned(),
                !$column->getNotNull() => $columnDefinition->nullable(),
                $column->getDefault() !== null => $columnDefinition->default($column->getDefault()),
                $column->getComment() !== null => $columnDefinition->comment($column->getComment()),
                $column->getLength() !== null => $columnDefinition->length($column->getLength()),
                $column->getPrecision() !== null => $columnDefinition->precision($column->getPrecision()),
                $column->getScale() !== null => $columnDefinition->scale($column->getScale()),
                $column->getFixed() => $columnDefinition->fixed(),
            };

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

    public function build(): self
    {
        return $this->buildColumns()
            ->buildForeignKeys()
            ->buildIndexes();
    }
}
