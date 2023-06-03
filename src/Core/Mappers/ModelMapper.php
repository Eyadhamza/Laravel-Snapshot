<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers;

use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\ColumnMapper;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignKeyMapper;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\IndexMapper;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
use Eyadhamza\LaravelEloquentMigration\Core\Generators\MigrationCommandGenerator;
use Eyadhamza\LaravelEloquentMigration\Core\Generators\MigrationGenerator;
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
    private ModelInfo $modelInfo;
    private MigrationCommandGenerator $generator;

    public function __construct(ModelInfo $modelInfo)
    {
        parent::__construct($modelInfo->tableName);
        $this->modelInfo = $modelInfo;
        $this->generator = MigrationCommandGenerator::make($this->tableName);
    }

    public static function make(ModelInfo $modelInfo): self
    {
        return new self($modelInfo);
    }

    public function map(): self
    {
        $this->indexes = $this->mapAttributes(IndexMapper::class, $this->modelInfo);

        $this->foreignKeys = $this->mapAttributes(ForeignKeyMapper::class, $this->modelInfo);

        $this->columns = $this->mapAttributes(ColumnMapper::class, $this->modelInfo);

        return $this;
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

    public function runGenerator(): MigrationGenerator
    {
        $this->generator
            ->run($this->columns, MigrationOperationEnum::Add)
            ->run($this->indexes, MigrationOperationEnum::Add)
            ->run($this->foreignKeys, MigrationOperationEnum::Add);

        return MigrationGenerator::make($this->tableName)
            ->setGeneratedCommands($this->generator->getGenerated());
    }
}
