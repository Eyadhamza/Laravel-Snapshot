<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Generators;

use Illuminate\Support\Str;

class MigrationGenerator extends Generator
{
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

}
