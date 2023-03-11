<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ColumnMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys\ForeignKeyMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
use Eyadhamza\LaravelAutoMigration\Core\Constants\MigrationOperation;
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


        $this->indexes = $this->mapAttributes(IndexMapper::class, $modelInfo);
        $this->foreignKeys = $this->mapAttributes(ForeignKeyMapper::class, $modelInfo);
        $this->columns = $this->mapAttributes(ColumnMapper::class, $modelInfo);
        $this->columns = $this->columns->merge($this->foreignKeys);
        $this->generator = new MigrationGenerator($modelInfo->tableName);
    }

    public static function make(ModelInfo $modelInfo): self
    {
        return new self($modelInfo);
    }

    private function mapAttributes(string $type, ModelInfo $modelInfo)
    {
        $reflection = new ReflectionClass($modelInfo->class);

        return collect($reflection->getAttributes())
            ->filter(fn(ReflectionAttribute $attribute) => is_subclass_of($attribute->getName(), $type))
            ->mapWithKeys(function (ReflectionAttribute $reflectionAttribute) use ($modelInfo) {
                $attribute = $reflectionAttribute
                    ->newInstance()
                    ->setType($reflectionAttribute->getName())
                    ->setDefinition($modelInfo->tableName);
                return [
                    $attribute->getName() => $attribute->getDefinition()
                ];
            });
    }


    public function map(): self
    {
        $this->mapColumns()
            ->mapIndexes()
            ->mapForeignKeys();
        return $this;
    }

    protected function mapColumns(): self
    {
        $this->columns
            ->each(fn(ColumnDefinition|Fluent $column) => $this->generator->generateAddedCommand($column, $column->get('name')));
        return $this;
    }


    protected function mapIndexes(): self
    {
        $this->indexes
            ->each(fn(IndexDefinition|Fluent $index) => $this->generator->generateAddedCommand($index, $index->get('columns')));
        return $this;
    }

    protected function mapForeignKeys(): self
    {
        $this->foreignKeys
            ->each(fn(ForeignKeyDefinition|Fluent $foreignKey) => $this->generator->generateAddedCommand($foreignKey, $foreignKey->get('columns')));
        return $this;
    }

    public function getMigrationGenerator(): MigrationGenerator
    {
        return $this->generator;
    }
}
