<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ColumnMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys\ForeignKeyMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Http\Resources\MissingValue;
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
                return $this
                    ->mapAttribute($attribute)
                    ->setDefinition($this->tableName);
            });
        $this->foreignKeys = $this->getAttributesOfForeignKeyType(new ReflectionClass($modelInfo->class))
            ->map(function (ReflectionAttribute $attribute) {
                return $this
                    ->mapAttribute($attribute)
                    ->setDefinition($this->tableName);
            });


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

    private function getAttributesOfForeignKeyType(ReflectionClass $param)
    {
        return collect($param->getAttributes())
            ->filter(function (ReflectionAttribute $attribute) {
                return is_subclass_of($attribute->getName(), ForeignKeyMapper::class);
            });
    }
    private function mapAttribute(ReflectionAttribute $reflectionAttribute): AttributeEntity
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
            return [
                $index->getName() => new IndexDefinition($this->mapToIndex($index))
            ];
        });
        return $this;
    }

    protected function mapForeignKeys(): self
    {
        $this->foreignKeys = $this->foreignKeys->mapWithKeys(function (ForeignKeyMapper $foreignKey) {
            return [
                $foreignKey->getName() => new ForeignKeyDefinition($this->mapToForeignKey($foreignKey))
            ];
        });
        return $this;
    }

    protected function mapToColumn(Column|AttributeEntity $column): array
    {
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

    protected function mapToIndex(Index|IndexMapper $index): array
    {
        $this->generator->addIndex($index);
        return [
            'name' => $index->getName(),
            'type' => $index->getType(),
            'columns' => $index->getColumns(),
        ];
    }

    protected function mapToForeignKey(ForeignKeyConstraint|ForeignKeyMapper $foreignKey): array
    {
        $rules = [];
        if ($foreignKey->getRules() === null) {
            return $rules;
        }
        foreach ($foreignKey->getRules() as $key => $value) {
            if (is_int($key)) {
                $rules[$value] = true;
                continue;
            }
            $rules[$key] = $value;
        }
        $this->generator->addForeignKey($foreignKey);
        return array_merge([
            'name' => $foreignKey->getName(),
            'type' => $foreignKey->getType(),
            'columns' => $foreignKey->getColumns(),
        ], $rules);
    }

    public function getMigrationGenerator(): MigrationGenerator
    {
        return $this->generator;
    }


}
