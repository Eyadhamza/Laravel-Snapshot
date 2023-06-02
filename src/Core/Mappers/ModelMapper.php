<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers;

use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\ColumnMapper;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignKeyMapper;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\IndexMapper;
use Eyadhamza\LaravelEloquentMigration\Core\Generators\MigrationCommandGenerator;
use Eyadhamza\LaravelEloquentMigration\Core\Generators\MigrationGenerator;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use ReflectionAttribute;
use ReflectionClass;
use Spatie\ModelInfo\ModelInfo;

class ModelMapper extends Mapper
{
    private MigrationCommandGenerator $generator;

    public function __construct(ModelInfo $modelInfo)
    {
        parent::__construct($modelInfo->tableName);

        $this->indexes = $this->mapAttributes(IndexMapper::class, $modelInfo);
        $this->foreignKeys = $this->mapAttributes(ForeignKeyMapper::class, $modelInfo);
        $this->columns = $this
            ->mapAttributes(ColumnMapper::class, $modelInfo)
            ->merge($this->foreignKeys);

        $this->generator = new MigrationCommandGenerator($modelInfo->tableName);
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
        $this->columns
            ->each(fn(ColumnDefinition|Fluent $column) => $this->generator->generateAddedCommand($column));

        $this->indexes
            ->each(fn(IndexDefinition|Fluent $index) => $this->generator->generateAddedCommand($index));
        return $this;
    }

    public function getMigrationGenerator(): MigrationGenerator
    {
        return MigrationGenerator::make($this->tableName)
            ->setGeneratedCommands($this->generator->getGenerated());
    }
}
