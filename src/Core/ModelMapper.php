<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ColumnMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use ReflectionAttribute;
use ReflectionClass;
use Spatie\ModelInfo\ModelInfo;

class ModelMapper extends Mapper
{
    private Collection $mappedBlueprint;
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

        $this->generator = new MigrationGenerator;
    }

    public static function make(ModelInfo $modelInfo): self
    {
        return new self($modelInfo);
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
        $this->columns = $this->columns->mapWithKeys(function (ColumnMapper $column) {
            return [
                $column->getName() => new Fluent($this->mapToColumn($column))
            ];
        });

        return $this;
    }

    public function map(): self
    {
        $this->mapColumns();
        return $this;
    }


    protected function mapIndexes(): self
    {
        $this->indexes = $this->indexes->mapWithKeys(function (IndexMapper $index) {
            return [
                $index->getName() => new Fluent($this->mapToIndex($index))
            ];
        });
        return $this;
    }

    protected function mapForeignKeys(): self
    {
        $this->foreignKeys = $this->foreignKeys->mapWithKeys(function (ForeignKeyConstraint $foreignKey) {
            return [
                $foreignKey->getName() => new Fluent($this->mapToForeignKey($foreignKey))
            ];
        });
        return $this;
    }

    protected function mapToColumn(AttributeEntity|Column $column): array
    {
        $rules = [];
        if ($column->getRules() === null) {
            return $rules;
        }
        foreach ($column->getRules() as $key => $value) {
            $this->generator->addColumn($column);
            if (is_int($key)) {
                $rules[$value] = true;
                continue;
            }
            $rules[$key] = $value;
        }
        return $rules;
    }

    protected function mapToIndex(Index|AttributeEntity $index): array
    {
        return [];
    }

    protected function mapToForeignKey(ForeignKeyConstraint|AttributeEntity $foreignKey): array
    {
        return [];
    }


}
