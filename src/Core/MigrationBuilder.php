<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\BlueprintColumnBuilder;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\BlueprintIndexBuilder;
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
            $attributes = $this->getAttributesOfColumnType(new ReflectionClass($model->class));

            $modelProperties = $attributes->map(function (ReflectionAttribute $attribute) {
                return $this->mapAttribute($attribute);
            });

            return ModelBlueprintBuilder::make(new Blueprint($model->tableName), $modelProperties)->build();
        });
        return $this;
    }

    public function buildMigrations(): self
    {
        $this->modelBlueprintBuilders->each(function (ModelBlueprintBuilder $modelBlueprintBuilder) {
            $tableName = $modelBlueprintBuilder->getTable();

            if (!Schema::hasTable($tableName)) {
                $this->generateFirstMigration($modelBlueprintBuilder);
                return $this;
            }
            $this->generateUpdatedMigration($modelBlueprintBuilder);

            return $this;
        });
        return $this;
    }

    public function getAttributesOfColumnType(ReflectionClass $reflectionClass): Collection
    {

        return collect($reflectionClass->getAttributes())
            ->filter(function (ReflectionAttribute $attribute) {
                return is_subclass_of($attribute->getName(), BlueprintColumnBuilder::class)
                    || is_subclass_of($attribute->getName(), BlueprintIndexBuilder::class);
            });

    }

    private function mapAttribute(ReflectionAttribute $reflectionAttribute): BlueprintColumnBuilder|BlueprintIndexBuilder
    {
        return $reflectionAttribute
            ->newInstance()
            ->setType($reflectionAttribute->getName());

    }
    private function generateFirstMigration(BlueprintBuilder $modelBlueprintBuilder): void
    {
        $migrationFile = $this->setMigrationFileAsCreateTemplate($modelBlueprintBuilder->getTable(), $modelBlueprintBuilder->getExecutionOrder());
        $this->generateMigrationFile($modelBlueprintBuilder, $migrationFile, 'create');
    }

    private function generateUpdatedMigration(BlueprintBuilder $modelBlueprintBuilder): void
    {
        $newBlueprint = $modelBlueprintBuilder->getBlueprint();
        $tableName = $newBlueprint->getTable();

        $blueprintOfCurrentTable = DoctrineBlueprintBuilder::make(new Blueprint($tableName))
            ->build()
            ->getBlueprint();

        $diffBlueprint = BlueprintComparer::make($blueprintOfCurrentTable, $newBlueprint)
            ->getDiff();
        if ($diffBlueprint->getMapped()->isNotEmpty()) {
            $migrationFile = $this->setMigrationFileAsUpdateTemplate($tableName, $modelBlueprintBuilder->getExecutionOrder());
            $this->generateMigrationFile($diffBlueprint, $migrationFile, 'update');
        }
    }
    private function generateMigrationFile(BlueprintBuilder|BlueprintComparer $mapToBlueprint, string $migrationFilePath,string $operation): void
    {
        $tableName = $mapToBlueprint->getTable();
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
        $this->modelBlueprintBuilders = $this->modelBlueprintBuilders->map(function (ModelBlueprintBuilder $model){
            $foreignKeyCommands = collect($model->getBlueprint()->getCommands())->filter(function ($command) {
                return $command->get('name') === 'foreign';
            });
            if ($foreignKeyCommands->isNotEmpty()) {
                $foreignKeyCommands->each(function ($command) use ($model) {
                    $relatedModel = $this->modelBlueprintBuilders->first(function ($modelWithForeign) use ($command) {
                        return $modelWithForeign->getBlueprint()->getTable() == $command->get('on');
                    });
                    if ($relatedModel) {
                        $model->setExecutionOrder($relatedModel->getExecutionOrder() + 1);
                    }
                });
            }
            return $model;
        })->sortBy(fn ($data) => $data->getExecutionOrder())->values();

        return $this;
    }
    public function getModelBlueprintBuilders(): Collection
    {
        return $this->modelBlueprintBuilders;
    }
}

