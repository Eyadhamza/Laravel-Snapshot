<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Closure;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionAttribute;
use ReflectionClass;
use Spatie\ModelInfo\ModelInfo;

class MigrationBuilder
{
    private ModelInfo $modelInfo;
    private BlueprintBuilder $modelBlueprintBuilder;

    public function __construct(ModelInfo $modelInfo)
    {
        $this->modelInfo = $modelInfo;
    }

    public static function make(ModelInfo $modelInfo): self
    {
        return new self($modelInfo);
    }

    public static function mapAll(Collection $models): Collection
    {
        return $models->map(function ($model) {
            return MigrationBuilder::make($model)
                ->mapModel()
                ->buildMigration();
        });
    }

    public function mapModel(): self
    {
        $attributes = $this->getAttributesOfColumnType(new ReflectionClass($this->modelInfo->class));

        $modelProperties = $attributes->map(function (ReflectionAttribute $attribute) {
            return $this->mapAttribute($attribute);
        });

        $this->modelBlueprintBuilder = ModelBlueprintBuilder::make(new Blueprint($this->modelInfo->tableName), $modelProperties)->build();

        return $this;
    }

    public function buildMigration(): self
    {

        $newBlueprint = $this->modelBlueprintBuilder->getBlueprint();
        $tableName = $this->modelBlueprintBuilder->getTable();

        if (!Schema::hasTable($tableName)) {
            $this->generateFirstMigration($this->modelBlueprintBuilder);
            return $this;
        }
        $this->generateUpdatedMigration($this->modelBlueprintBuilder);

        return $this;
    }

    public function getAttributesOfColumnType(ReflectionClass $reflectionClass): Collection
    {

        return collect($reflectionClass->getAttributes())
            ->filter(fn($attribute) => is_subclass_of($attribute->getName(), Column::class));

    }

    private function mapAttribute(ReflectionAttribute $attribute): Column
    {
        $rules = $attribute
            ->newInstance()
            ->getRules();

        $propertyType = $attribute->getName();

        return $attribute
            ->newInstance()
            ->setName($attribute->getArguments()[0])
            ->setType($propertyType)
            ->setRules($rules);

    }
    private function generateFirstMigration(BlueprintBuilder $modelBlueprintBuilder): void
    {
        $migrationFile = $this->setMigrationFileAsCreateTemplate($modelBlueprintBuilder->getTable());
        $this->generateMigrationFile($modelBlueprintBuilder, $migrationFile, 'create');
    }

    private function generateUpdatedMigration(BlueprintBuilder $modelBlueprintBuilder)
    {
        $newBlueprint = $modelBlueprintBuilder->getBlueprint();
        $tableName = $newBlueprint->getTable();

        $migrationFile = $this->setMigrationFileAsUpdateTemplate($tableName);

        $blueprintOfCurrentTable = DoctrineBlueprintBuilder::make(new Blueprint($tableName))
            ->build()
            ->getBlueprint();

        $diffBlueprint = BlueprintComparer::make($blueprintOfCurrentTable, $newBlueprint)
            ->getDiff();

        $this->generateMigrationFile($diffBlueprint, $migrationFile, 'update');
    }
    private function generateMigrationFile(BlueprintBuilder|BlueprintComparer $mapToBlueprint, string $migrationFilePath,string $operation): void
    {
        $oldMigrationFile = file_get_contents($migrationFilePath);
        $tableName = $mapToBlueprint->getBlueprint()->getTable();

        $generatedMigrationFile = $this->replaceStubMigrationFile($tableName, $mapToBlueprint->getMapped(), $operation);

        file_put_contents($migrationFilePath, $generatedMigrationFile);

    }


    private function replaceStubMigrationFile($tableName, $mappedColumns, string $operation): string
    {
        $fileContent = file_get_contents("stubs/{$operation}-migration.stub");
        $fileContent = Str::replace("\$tableName", $tableName, $fileContent);

        return Str::replace("{{ \$mappedColumns }}",$mappedColumns->join("\n \t \t \t"), $fileContent);
    }

    private function setMigrationFileAsCreateTemplate(string $tableName)
    {
        return app('migration.creator')->create("create_{$tableName}_table", database_path('migrations'), $tableName, true);
    }

    private function setMigrationFileAsUpdateTemplate(string $tableName): string
    {
        return app('migration.creator')->create("update_{$tableName}_table", database_path('migrations'), $tableName);
    }



}

