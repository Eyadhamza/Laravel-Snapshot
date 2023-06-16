<?php

namespace PiSpace\LaravelSnapshot\Core\Comparer;

use PiSpace\LaravelSnapshot\Core\Constants\MigrationOperationEnum;
use PiSpace\LaravelSnapshot\Core\Generators\MigrationCommandGenerator;
use PiSpace\LaravelSnapshot\Core\Generators\MigrationGenerator;
use PiSpace\LaravelSnapshot\Core\Mappers\DoctrineMapper;
use PiSpace\LaravelSnapshot\Core\Mappers\Mapper;
use PiSpace\LaravelSnapshot\Core\Mappers\ModelMapper;
use Illuminate\Support\Collection;

class ComparerManager extends Mapper
{
    private DoctrineMapper $doctrineMapper;
    private ModelMapper $modelMapper;
    private MigrationCommandGenerator $generator;

    public function __construct(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper)
    {
        parent::__construct($doctrineMapper->getTableName());
        $this->doctrineMapper = $doctrineMapper;
        $this->modelMapper = $modelMapper;
        $this->generator = MigrationCommandGenerator::make($this->tableName);

    }

    public function map(): Mapper
    {
        // TODO: Error is here
        $this->columns = $this->compareElements($this->modelMapper->getColumns(), $this->doctrineMapper->getColumns());

        $this->foreignKeys = $this->compareElements($this->modelMapper->getForeignKeys(), $this->doctrineMapper->getForeignKeys());

        $this->indexes = $this->compareElements($this->modelMapper->getIndexes(), $this->doctrineMapper->getIndexes());
        return $this;
    }
    public static function make(DoctrineMapper $doctrineMapper, ModelMapper $modelMapper): ComparerManager
    {
        return new self($doctrineMapper, $modelMapper);
    }

    public function runGenerator(): MigrationGenerator
    {
        $this->columns->each(fn($columns, $operation) => $this->generator->run($columns, MigrationOperationEnum::from($operation)));

        $this->foreignKeys->each(fn($foreignKeys, $operation) => $this->generator->run($foreignKeys, MigrationOperationEnum::from($operation)));

        $this->indexes->each(fn($indexes, $operation) => $this->generator->run($indexes, MigrationOperationEnum::from($operation)));

        return MigrationGenerator::make($this->tableName)
            ->setGeneratedCommands($this->generator->getGenerated());
    }


    private function compareElements(Collection $modelElements, Collection $doctrineElements): Collection
    {
        return ElementComparer::make()
            ->setModelElements($modelElements)
            ->setDoctrineElements($doctrineElements)
            ->run()
            ->getElements();
    }

}
