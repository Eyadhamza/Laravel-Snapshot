<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ColumnMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys\ForeignKeyMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
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


        $this->columns = $this->mapAttributes(ColumnMapper::class, $modelInfo);
        $this->indexes = $this->mapAttributes(IndexMapper::class, $modelInfo);

        $this->foreignKeys = $this->mapAttributes(ForeignKeyMapper::class, $modelInfo);

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
                $attribute = $reflectionAttribute->newInstance();
                return [
                    $attribute->getName() => $attribute
                        ->setType($reflectionAttribute->getName())
                        ->setDefinition($modelInfo->tableName)
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
            ->each(fn(ColumnMapper $column) => $this->generator->generateAddedCommand($column, $column->getName()));
        return $this;
    }


    protected function mapIndexes(): self
    {
        $this->indexes
            ->each(fn(IndexMapper $index) => $this->generator->generateAddedCommand($index, $index->getColumns()));
        return $this;
    }

    protected function mapForeignKeys(): self
    {
        $this->foreignKeys
            ->each(fn(ForeignKeyMapper $foreignKey) => $this->generator->generateAddedCommand($foreignKey, $foreignKey->getColumns()));
        return $this;
    }

    public function getMigrationGenerator(): MigrationGenerator
    {
        return $this->generator;
    }
}
