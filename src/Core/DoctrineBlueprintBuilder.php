<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DoctrineBlueprintBuilder extends BlueprintBuilder
{
    private Table $doctrineTableDetails;
    protected $typeMappings = [
        'bit' => 'string',
        'citext' => 'string',
        'enum' => 'string',
        'geometry' => 'string',
        'geomcollection' => 'string',
        'linestring' => 'string',
        'ltree' => 'string',
        'multilinestring' => 'string',
        'multipoint' => 'string',
        'multipolygon' => 'string',
        'point' => 'string',
        'polygon' => 'string',
        'sysname' => 'string',
    ];

    public function __construct(Blueprint $blueprint)
    {
        parent::__construct($blueprint);

        $schema =  Schema::getConnection()->getDoctrineSchemaManager();

        $this->registerTypeMappings($schema->getDatabasePlatform());

        $this->doctrineTableDetails = $schema->introspectTable($blueprint->getTable());

    }

    public static function make(Blueprint $blueprint): self
    {
        return new self($blueprint);
    }

    public function buildColumns(): static
    {
        collect($this->doctrineTableDetails->getColumns())->map(function (Column $column) {
            return $this->blueprint->addColumn(
                    $this->mapTypeToBlueprint($column),
                    $column->getName(),
                    $this->mapAttributesToBlueprint($column)
                );
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

    protected function registerTypeMappings(AbstractPlatform $platform)
    {
        foreach ($this->typeMappings as $type => $value) {
            $platform->registerDoctrineTypeMapping($type, $value);
        }
    }

    private function mapAttributesToBlueprint(Column $column)
    {
        return collect([
            'name' => $column->getName(),
            'unsigned' => $column->getUnsigned(),
            'nullable' => $column->getNotnull() == false,
            'default' => $column->getDefault(),
            'length' => $column->getLength(),
            'precision' => $column->getPrecision(),
            'scale' => $column->getScale(),
            'comment' => $column->getComment(),
            'autoIncrement' => $column->getAutoincrement(),
            'fixed' => $column->getFixed(),
        ])->filter()->toArray();
    }

    private function mapTypeToBlueprint(Column $column): string
    {
        return match ($column->getType()->getName()) {
            'datetime' => 'timestamp',
            'bigint' => 'bigInteger',
            'smallint' => 'smallInteger',
            'tinyint' => 'tinyInteger',
            default => $column->getType()->getName(),
        };
    }

}
