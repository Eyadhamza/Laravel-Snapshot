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

class MapToMigration
{

    /**
     * @var Collection<Model, Blueprint>
     */
    private Collection $modelBlueprintsBuilders;

    public function __construct()
    {

        $this->modelBlueprintsBuilders = collect();

        $this->mapModels(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));

    }

    public static function make(): self
    {
        return new self();
    }

    public function mapModels(Collection $models): self
    {

        $models->map(function ($model) {
            $attributes = $this->getAttributesOfColumnType(new ReflectionClass($model->class));

            $modelProperties = $attributes->map(function (ReflectionAttribute $attribute) {
                return $this->mapAttribute($attribute);
            });

            $this->modelBlueprintsBuilders->put($model->class, ModelBlueprintBuilder::make(new Blueprint($model->tableName), $modelProperties)->build());
        });

        return $this;
    }

    public function buildMigrations(): self
    {
        $this->modelBlueprintsBuilders->each(function (ModelBlueprintBuilder $modelBlueprintBuilder) {
            $newBlueprint = $modelBlueprintBuilder->getBlueprint();
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

    public function getModelBlueprintsBuilders(): Collection
    {
        return $this->modelBlueprintsBuilders;
    }

    private function generateMigrationFile(BlueprintBuilder $mapToBlueprint, string $migrationFilePath): self
    {
        $oldMigrationFile = file_get_contents($migrationFilePath);
        $tableName = $mapToBlueprint->getBlueprint()->getTable();

        $generatedMigrationFile = Str::replace($this->oldStubMigrationFile($tableName), $this->newMigrationFile($tableName, $mapToBlueprint->getMappedColumns()), $oldMigrationFile);

        file_put_contents($migrationFilePath, $generatedMigrationFile);

        return $this;
    }


    private function oldStubMigrationFile($tableName): string
    {
        return "    public function up()
    {
        Schema::create('$tableName', function (Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
        });
    }";
    }

    private function newMigrationFile(string $tableName, Collection $mappedColumns): string
    {
        return
            "
    public function up()
    {
        Schema::create('$tableName', function (Blueprint \$table) {
            {$mappedColumns->join("\n \t \t \t")}
            \$table->timestamps();
        });
    }
            ";
    }

    public function getBlueprints()
    {
        return $this->modelBlueprintsBuilders->map(function (BlueprintBuilder $mapToBlueprint) {
            return $mapToBlueprint->getBlueprint();
        });
    }


    private function setMigrationFileAsCreateTemplate(string $tableName)
    {
        return app('migration.creator')->create("create_{$tableName}_table", database_path('migrations'), $tableName, true);
    }

    private function setMigrationFileAsUpdateTemplate(string $tableName): string
    {
       return app('migration.creator')->create("update_{$tableName}_table", database_path('migrations'), $tableName);
    }

    private function generateFirstMigration(ModelBlueprintBuilder $modelBlueprintBuilder): void
    {
        $migrationFile = $this->setMigrationFileAsCreateTemplate($modelBlueprintBuilder->getTable());
        $this->generateMigrationFile($modelBlueprintBuilder, $migrationFile);
    }

    private function generateUpdatedMigration(ModelBlueprintBuilder $modelBlueprintBuilder)
    {
        $newBlueprint = $modelBlueprintBuilder->getBlueprint();
        $tableName = $newBlueprint->getTable();

        $migrationFile = $this->setMigrationFileAsUpdateTemplate($tableName);

        $blueprintOfCurrentTable = DoctrineBlueprintBuilder::make(new Blueprint($tableName))
            ->build()
            ->getBlueprint();

        $diffBlueprint = BlueprintComparer::make($blueprintOfCurrentTable, $newBlueprint)
            ->getDiffBlueprint();

        dd($diffBlueprint);

        $this->generateMigrationFile($mapToBlueprint, $migrationFile);
    }

}

