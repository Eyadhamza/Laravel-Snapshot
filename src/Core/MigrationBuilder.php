<?php

namespace PiSpace\LaravelSnapshot\Core;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use PiSpace\LaravelSnapshot\Core\Comparer\ComparerManager;
use PiSpace\LaravelSnapshot\Core\Mappers\DoctrineMapper;
use PiSpace\LaravelSnapshot\Core\Mappers\ModelMapper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Spatie\ModelInfo\ModelInfo;

class MigrationBuilder
{
    private Collection $modelMappers;

    public function __construct(Collection $modelBlueprintBuilder)
    {
        $this->modelMappers = $modelBlueprintBuilder;
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
        $this->modelMappers = $this->modelMappers->map(function (ModelInfo $model) {
            return ModelMapper::make($model)->map();
        });
        return $this;
    }

    public function buildMigrations(): self
    {
        $this->modelMappers->map(function (ModelMapper $modelMapper) {
            $tableName = $modelMapper->getTableName();
            if (!Schema::hasTable($tableName)) {
                $this->buildFirstMigration($modelMapper);
                return $this;
            }
            $this->buildUpdatedMigration($modelMapper);

            return $this;
        });
        return $this;
    }


    private function buildFirstMigration(ModelMapper $modelMapper): void
    {
        $tableName = $modelMapper->getTableName();
        $migrationFile = $this->setMigrationFileAsCreateTemplate($tableName, $modelMapper->getExecutionOrder());
        $generator = $modelMapper->runGenerator();
        $generator->generateMigrationFile($migrationFile, 'create');
    }

    private function buildUpdatedMigration(ModelMapper $modelMapper): void
    {
        $tableName = $modelMapper->getTableName();
        $doctrineMapper = DoctrineMapper::make($tableName)->map();
        $generator = ComparerManager::make($doctrineMapper, $modelMapper)
            ->map()
            ->runGenerator();
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
        $modelMappers = collect();
        $processed = collect();

        while ($this->modelMappers->isNotEmpty()) {
            $this->modelMappers->each(function (ModelMapper $model) use ($modelMappers, $processed) {
                if ($model->getForeignKeys()->isEmpty() || $model->getForeignKeys()->every(function (ForeignKeyConstraint $key) use ($processed) {
                        return $processed->contains($key->getForeignTableName()) || $key->getForeignTableName() === null;
                    })) {
                    $modelMappers->push($model);
                    $processed->push($model->getTableName());
                    $this->modelMappers = $this->modelMappers->reject(function ($m) use ($model) {
                        return $m->getTableName() === $model->getTableName();
                    });
                }
            });
        }

        $this->modelMappers = $modelMappers->values();
        $this->modelMappers->each(function (ModelMapper $model, $index) {
            $model->setExecutionOrder($index + 1);
        });

        return $this;
    }

    public function getModelMappers(): Collection
    {
        return $this->modelMappers;
    }
}

