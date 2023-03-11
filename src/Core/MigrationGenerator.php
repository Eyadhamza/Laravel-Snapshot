<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Constants\MigrationOperation;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class MigrationGenerator
{
    private Collection $generated;
    private string $tableName;

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
        $this->generated = new Collection;
    }

    public static function make(string $tableName): MigrationGenerator
    {
        return new self($tableName);
    }

    public function generateCommand(Fluent $column, $operation): self
    {
        $commandFormatter = MigrationFormatter::make($column)
            ->setOperation($operation)
            ->setRules($this->getRules($column))
            ->run();

        $this->generated->add($commandFormatter);
        return $this;
    }

    public function generateAddedCommand(Fluent $column): self
    {
        return $this->generateCommand($column, MigrationOperation::Add);
    }

    public function generateRemovedCommand(Fluent $column): self
    {
        return $this->generateCommand($column, MigrationOperation::Remove);
    }

    public function generateModifiedCommand(Fluent $column): self
    {
        return $this->generateCommand($column, MigrationOperation::Modify);
    }
    public function generateMigrationFile(string $migrationFilePath, string $operation): void
    {
        $generatedMigrationFile = $this->replaceStubMigrationFile($operation);
        file_put_contents($migrationFilePath, $generatedMigrationFile);
    }

    private function replaceStubMigrationFile(string $operation): string
    {
        $fileContent = file_get_contents("stubs/$operation-migration.stub");
        $fileContent = Str::replace("\$tableName", $this->tableName, $fileContent);

        return Str::replace("{{ \$mappedColumns }}", $this->getGenerated()->join("\n \t \t \t"), $fileContent);
    }

    public function getGenerated()
    {
        return $this->generated->flatten()->filter()->values();
    }

    private function getRules(Fluent $column): array
    {
        return array_filter($column->getAttributes(), fn($key,) => !in_array($key, ['type', 'name', 'columns']), ARRAY_FILTER_USE_KEY);
    }

}
