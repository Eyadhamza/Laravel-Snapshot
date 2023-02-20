<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ColumnMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionAttribute;
use ReflectionClass;
use Spatie\ModelInfo\ModelInfo;

class MigrationBuilder
{
    private Collection $modelBlueprintBuilders;

    public function __construct(Collection $modelBlueprintBuilder)
    {
        $this->modelBlueprintBuilders = $modelBlueprintBuilder;
    }

    public static function make(Collection $modelBlueprintBuilder): self
    {
        return new self($modelBlueprintBuilder);
    }

    public static function mapAll(Collection $models): self
    {

        return MigrationBuilder::make($models)
            ->mapModels()
            ->ensureExecutionOrder()
            ->buildMigrations();

    }

    public function mapModels(): self
    {
        $this->modelBlueprintBuilders = $this->modelBlueprintBuilders->map(function (ModelInfo $model) {
            return ModelMapper::make($model)->map();
        });
        return $this;
    }

    public function buildMigrations(): self
    {
        $this->modelBlueprintBuilders->each(function (ModelMapper $modelBlueprintBuilder) {
            $tableName = $modelBlueprintBuilder->getTableName();

            if (!Schema::hasTable($tableName)) {
                $this->generateFirstMigration($modelBlueprintBuilder);
                return $this;
            }
            $this->generateUpdatedMigration($modelBlueprintBuilder);

            return $this;
        });
        return $this;
    }


    private function generateFirstMigration(Mapper $modelBlueprintBuilder): void
    {
        $migrationFile = $this->setMigrationFileAsCreateTemplate($modelBlueprintBuilder->getTableName(), $modelBlueprintBuilder->getExecutionOrder());
        $this->generateMigrationFile($modelBlueprintBuilder, $migrationFile, 'create');
    }

    private function generateUpdatedMigration(Mapper $modelMapper): void
    {
        $tableName = $modelMapper->getTableName();

        $doctineMapper = DoctrineMapper::make($tableName)
            ->map();

        $diffBlueprint = Comparer::make($doctineMapper, $modelMapper)->getDiff();
        if ($diffBlueprint->getMapped()->isNotEmpty()) {
            $migrationFile = $this->setMigrationFileAsUpdateTemplate($tableName, $modelMapper->getExecutionOrder());
            $this->generateMigrationFile($diffBlueprint, $migrationFile, 'update');
        }
    }
    private function generateMigrationFile(Mapper|Comparer $mapToBlueprint, string $migrationFilePath, string $operation): void
    {
        $tableName = $mapToBlueprint->getTableName();
        $generatedMigrationFile = $this->replaceStubMigrationFile($tableName, $mapToBlueprint->getMapped(), $operation);
        file_put_contents($migrationFilePath, $generatedMigrationFile);
    }


    private function replaceStubMigrationFile($tableName, $mappedColumns, string $operation): string
    {
        $fileContent = file_get_contents("stubs/{$operation}-migration.stub");
        $fileContent = Str::replace("\$tableName", $tableName, $fileContent);

        return Str::replace("{{ \$mappedColumns }}",$mappedColumns->join("\n \t \t \t"), $fileContent);
    }

    private function setMigrationFileAsCreateTemplate(string $tableName, int $executionOrder)
    {
        return app('migration.creator')->create($executionOrder . '_' . "create_{$tableName}_table", database_path('migrations'), $tableName, true);
    }

    private function setMigrationFileAsUpdateTemplate(string $tableName, int $executionOrder): string
    {
        return app('migration.creator')->create($executionOrder . '_' . "update_{$tableName}_table", database_path('migrations'), $tableName);
    }
    private function ensureExecutionOrder(): self
    {
        $this->modelBlueprintBuilders = $this->modelBlueprintBuilders->map(function (ModelMapper $model){

                $model->getForeignKeys()->each(function ($command) use ($model) {
                    $relatedModel = $this->modelBlueprintBuilders->first(function ($modelWithForeign) use ($command) {
                        return $modelWithForeign->getBlueprint()->getTable() == $command->get('on');
                    });
                    if ($relatedModel) {
                        $model->setExecutionOrder($relatedModel->getExecutionOrder() + 1);
                    }
                });
            return $model;
        })->sortBy(fn ($data) => $data->getExecutionOrder())->values();

        return $this;
    }
    public function getModelBlueprintBuilders(): Collection
    {
        return $this->modelBlueprintBuilders;
    }
}

