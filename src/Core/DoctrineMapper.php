<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DoctrineMapper extends Mapper
{
    public function __construct(string $tableName)
    {
        parent::__construct($tableName);

        $schema = Schema::getConnection()->getDoctrineSchemaManager();

        $this->registerTypeMappings($schema->getDatabasePlatform());

        $doctrineTableDetails = $schema->introspectTable($tableName);

        $this->columns = collect($doctrineTableDetails->getColumns());

        $this->indexes = collect($doctrineTableDetails->getIndexes())
            ->reject(fn(Index $index) => $index->isPrimary())
            ->reject(fn(Index $index) => Str::contains($index->getName(), 'foreign'));

        $this->foreignKeys = collect($doctrineTableDetails->getForeignKeys());
    }

    public function map(): self
    {

        return $this
            ->mapColumns()
            ->mapForeignKeys()
            ->mapIndexes();
    }

    public static function make(string $tableName): self
    {
        return new self($tableName);
    }

    public function mapColumns(): static
    {
        $this->columns = $this
            ->columns
            ->map(fn(Column $column) => new ColumnDefinition($this->mapToColumn($column)));
        return $this;
    }

    public function mapIndexes(): static
    {
        $this->indexes = $this->indexes
            ->map(fn(Index $index) => new IndexDefinition($this->mapAttributesToIndex($index)));
        return $this;
    }

    public function mapForeignKeys(): static
    {
        $this->foreignKeys = $this->foreignKeys
            ->mapWithKeys(fn(ForeignKeyConstraint $foreignKey) => [
                $foreignKey->getLocalColumns()[0] => new ForeignKeyDefinition($this->mapToForeignKey($foreignKey))
            ]);
        return $this;
    }

    private function mapAttributesToIndex(Index $index): array
    {
        return collect([
            'primary' => $index->isPrimary(),
            'unique' => $index->isUnique(),
            'name' => $index->getName(),
            'algorithm' => $index->getFlags(),
        ])->filter()->toArray();
    }


    protected function registerTypeMappings(AbstractPlatform $platform)
    {
        foreach ($this->typeMappings as $type => $value) {
            $platform->registerDoctrineTypeMapping($type, $value);
        }
    }

    protected function mapToColumn(Column|AttributeEntity $column): array
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

    private function mapToColumnType(Column|AttributeEntity $column): string
    {
        return match ($column->getType()->getName()) {
            'datetime' => 'timestamp',
            'bigint' => 'bigInteger',
            'smallint' => 'smallInteger',
            'tinyint' => 'tinyInteger',
            default => $column->getType()->getName(),
        };
    }

    protected function mapToForeignKey(ForeignKeyConstraint|AttributeEntity $foreignKey): array
    {
        return collect([
            'name' => $foreignKey->getName(),
            'constrained' => $foreignKey->getForeignTableName(),
            'columns' => $foreignKey->getLocalColumns(),
            'cascadeOnDelete' => $foreignKey->getOption('onDelete') == 'CASCADE',
            'cascadeOnUpdate' => $foreignKey->getOption('onUpdate') == 'CASCADE',
        ])->filter()->toArray();
    }

    public function mapToIndex(Index|AttributeEntity $index): array
    {
        return collect([
            'name' => $index->getName(),
            'columns' => $index->getColumns(),
            'unique' => $index->isUnique(),
            'algorithm' => $index->getFlags(),
        ])->filter()->toArray();
    }
}
