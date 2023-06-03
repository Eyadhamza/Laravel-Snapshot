<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DoctrineMapper extends Mapper
{
    private Table $doctrineTableDetails;
    private AbstractSchemaManager $schema;

    public function __construct(string $tableName)
    {
        parent::__construct($tableName);

        $this->schema = Schema::getConnection()->getDoctrineSchemaManager();

        $this
            ->setDoctrineTableDetails()
            ->registerTypeMappings()
            ->initializeMapper();
    }

    public function map(): self
    {
        return $this;
    }

    public static function make(string $tableName): self
    {
        return new self($tableName);
    }

    protected function registerTypeMappings(): self
    {
        $platform = $this->schema->getDatabasePlatform();

        foreach ($this->typeMappings as $type => $value) {
            $platform->registerDoctrineTypeMapping($type, $value);
        }

        return $this;
    }

    private function setDoctrineTableDetails(): self
    {
        $this->doctrineTableDetails = $this->schema->listTableDetails($this->tableName);

        return $this;
    }

    private function initializeMapper(): self
    {
        $this->columns = collect($this->doctrineTableDetails->getColumns());

        $this->indexes = collect($this->doctrineTableDetails->getIndexes())
            ->reject(fn(Index $index) => $index->isPrimary())
            ->reject(fn(Index $index) => Str::contains($index->getName(), 'foreign'));

        $this->foreignKeys = collect($this->doctrineTableDetails->getForeignKeys());

        return $this;
    }

}
