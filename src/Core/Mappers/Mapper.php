<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers;

use Illuminate\Support\Collection;

abstract class Mapper
{
    protected int $executionOrder = 1;
    protected string $tableName;
    protected Collection $columns;
    protected Collection $indexes;
    protected Collection $foreignKeys;

    protected array $typeMappings = [
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
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    abstract public function map(): self;
    public function getForeignKeys(): Collection
    {
        return $this->foreignKeys;
    }

    public function getIndexes(): Collection
    {
        return $this->indexes;
    }

    public function getColumns(): Collection
    {
        return $this->columns;
    }


    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getExecutionOrder(): int
    {
        return $this->executionOrder;
    }

    public function setExecutionOrder(int $executionOrder): static
    {
        $this->executionOrder = $executionOrder;
        return $this;
    }

    public function setColumns($columns): static
    {
        $this->columns = $columns;
        return $this;
    }

    public function setIndexes($indexes): static
    {
        $this->indexes = $indexes;
        return $this;
    }

    public function setForeignKeys($foreignKeys): static
    {
        $this->foreignKeys = $foreignKeys;
        return $this;
    }
}
