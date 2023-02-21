<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ColumnMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use ReflectionAttribute;
use ReflectionClass;
use Spatie\ModelInfo\ModelInfo;

class ModelMapper extends Mapper
{
    private MigrationGenerator $generator;

    public function __construct(ModelInfo $modelInfo)
    {
        parent::__construct($modelInfo->tableName);

        $attributes = $this->getAttributesOfColumnType(new ReflectionClass($modelInfo->class));

        $this->columns = $attributes->map(function (ReflectionAttribute $attribute) {
            return $this->mapAttribute($attribute);
        });

        $this->indexes = $this->getAttributesOfIndexType(new ReflectionClass($modelInfo->class))
            ->map(function (ReflectionAttribute $attribute) {
                return $this->mapAttribute($attribute);
            });

        $this->foreignKeys = collect();

        $this->mappedBlueprint = collect();

        $this->generator = new MigrationGenerator($modelInfo->tableName);
    }

    public static function make(ModelInfo $modelInfo): self
    {
        return new self($modelInfo);
    }

    public function map(): self
    {
        $this
            ->mapColumns()
            ->mapIndexes()
            ->mapForeignKeys();
        return $this;
    }

    public function getAttributesOfColumnType(ReflectionClass $reflectionClass): Collection
    {
        return collect($reflectionClass->getAttributes())
            ->filter(function (ReflectionAttribute $attribute) {
                return is_subclass_of($attribute->getName(), ColumnMapper::class);
            });
    }

    public function getAttributesOfIndexType(ReflectionClass $reflectionClass): Collection
    {

        return collect($reflectionClass->getAttributes())
            ->filter(function (ReflectionAttribute $attribute) {
                return is_subclass_of($attribute->getName(), IndexMapper::class);
            });
    }

    private function mapAttribute(ReflectionAttribute $reflectionAttribute): ColumnMapper|IndexMapper
    {
        return $reflectionAttribute
            ->newInstance()
            ->setType($reflectionAttribute->getName());

    }

    protected function mapColumns(): self
    {
        $this->columns = $this->columns->mapWithKeys(function (ColumnMapper|Fluent $column) {
            return [
                $column->getName() => new ColumnDefinition($this->mapToColumn($column))
            ];
        });

        return $this;
    }


    protected function mapIndexes(): self
    {
        $this->indexes = $this->indexes->mapWithKeys(function (IndexMapper $index) {
            return new IndexDefinition($this->mapToIndex($index));
        });
        return $this;
    }

    protected function mapForeignKeys(): self
    {
        $this->foreignKeys = $this->foreignKeys->mapWithKeys(function (ForeignKeyConstraint $foreignKey) {
            return new ForeignKeyDefinition($this->mapToForeignKey($foreignKey));
        });
        return $this;
    }

    protected function mapToColumn(AttributeEntity|Column $column): array
    {
        dump($column);
        $rules = [];
        $rules['type'] = $column->getType();
        $rules['name'] = $column->getName();
        if ($column->getRules() === null) {
            return $rules;
        }
        foreach ($column->getRules() as $key => $value) {
            if (is_int($key)) {
                $rules[$value] = true;
                continue;
            }
            $rules[$key] = $value;
        }
        $this->generator->addColumn($column);
        return $rules;
    }

    protected function mapToIndex(Index|AttributeEntity $index): array
    {
        $this->generator->addIndex($index);
        return [
            'name' => $index->getName(),
            'type' => $index->getType(),
            'columns' => $index->getColumns(),
        ];
    }

    protected function mapToForeignKey(ForeignKeyConstraint|AttributeEntity $foreignKey): array
    {
        $this->generator->addForeignKey($foreignKey);
        return [
            'name' => $foreignKey->getName(),
            'columns' => $foreignKey->getColumns(),
            'referencedTable' => $foreignKey->getForeignTableName(),
            'referencedColumns' => $foreignKey->getForeignColumns(),
            'onDelete' => $foreignKey->onDelete(),
            'onUpdate' => $foreignKey->onUpdate(),
        ];
    }

    public function getMigrationGenerator(): MigrationGenerator
    {
        return $this->generator;
    }

}
