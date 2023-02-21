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
                $this->buildFirstMigration($modelBlueprintBuilder);
                return $this;
            }
            $this->buildUpdatedMigration($modelBlueprintBuilder);

            return $this;
        });
        return $this;
    }


    private function buildFirstMigration(ModelMapper $modelMapper): void
    {
        $tableName = $modelMapper->getTableName();
        $migrationFile = $this->setMigrationFileAsCreateTemplate($tableName, $modelMapper->getExecutionOrder());
        $generator = $modelMapper->getMigrationGenerator();
        $generator->generateMigrationFile($migrationFile, 'create');
    }

    private function buildUpdatedMigration(ModelMapper $modelMapper): void
    {
        $tableName = $modelMapper->getTableName();
        $doctrineMapper = DoctrineMapper::make($tableName)->map();
        $generator = Comparer::make($doctrineMapper, $modelMapper)->getMigrationGenerator();
        if ($generator->getGenerated()->isNotEmpty()) {
            $migrationFile = $this->setMigrationFileAsUpdateTemplate($tableName, $modelMapper->getExecutionOrder());
            $generator->generateMigrationFile($migrationFile, 'update');
        }
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
        $this->modelBlueprintBuilders = $this->modelBlueprintBuilders->map(function (ModelMapper $model) {
            $model->getForeignKeys()->each(function ($command) use ($model) {
                $relatedModel = $this->modelBlueprintBuilders->first(function ($modelWithForeign) use ($command) {
                    return $modelWithForeign->getBlueprint()->getTable() == $command->get('on');
                });
                if ($relatedModel) {
                    $model->setExecutionOrder($relatedModel->getExecutionOrder() + 1);
                }
            });
            return $model;
        })->sortBy(fn($data) => $data->getExecutionOrder())->values();

        return $this;
    }

    public function getModelBlueprintBuilders(): Collection
    {
        return $this->modelBlueprintBuilders;
    }
}

