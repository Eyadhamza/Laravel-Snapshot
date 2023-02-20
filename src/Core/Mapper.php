<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Illuminate\Support\Collection;

abstract class Mapper
{

    protected int $executionOrder = 0;
    private string $tableName;
    protected Collection $columns;
    protected Collection $indexes;
    protected Collection $foreignKeys;

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

    abstract protected function mapColumns(): self;
    abstract protected function mapIndexes(): self;
    abstract protected function mapForeignKeys(): self;
    abstract protected function mapToColumn(Column|AttributeEntity $column): array;
    abstract protected function mapToIndex(Index|AttributeEntity $index): array;
    abstract protected function mapToForeignKey(ForeignKeyConstraint|AttributeEntity $foreignKey): array;
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
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
}
