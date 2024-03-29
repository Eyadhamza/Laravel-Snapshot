<?php

namespace PiSpace\LaravelSnapshot\Core\Mappers;

use PiSpace\LaravelSnapshot\Core\Attributes\Columns\ColumnMapper;
use PiSpace\LaravelSnapshot\Core\Attributes\ForeignKeys\ForeignKeyMapper;
use PiSpace\LaravelSnapshot\Core\Attributes\Indexes\IndexMapper;
use PiSpace\LaravelSnapshot\Core\Constants\MigrationOperationEnum;
use PiSpace\LaravelSnapshot\Core\Generators\MigrationCommandGenerator;
use PiSpace\LaravelSnapshot\Core\Generators\MigrationFileGenerator;
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
                    ->setDefinition($modelInfo->tableName);

                $attribute->getDefinition()->laravelType = $attribute->getType();

                return [
                    $attribute->getName() => $attribute->getDefinition()
                ];
            });
    }

    public function runGenerator(): MigrationFileGenerator
    {
        $this->columns = ElementToCommandMapper::collection($this->columns);
        $this->indexes = ElementToCommandMapper::collection($this->indexes);
        $this->foreignKeys = ElementToCommandMapper::collection($this->foreignKeys);

        $this->generator
            ->run($this->columns, MigrationOperationEnum::Add)
            ->run($this->indexes, MigrationOperationEnum::Add)
            ->run($this->foreignKeys, MigrationOperationEnum::Add);

        return MigrationFileGenerator::make($this->tableName)
            ->setGeneratedCommands($this->generator->getGenerated());
    }
}
