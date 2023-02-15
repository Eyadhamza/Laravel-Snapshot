<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionAttribute;
use ReflectionClass;
use Spatie\ModelInfo\ModelInfo;

class MapToMigration
{


    protected Application $laravel;

    protected MigrationCreator $creator;

    protected Composer $composer;

    /**
     * @var Collection<Model, Blueprint>
     */
    private Collection $blueprintsMappers;

    public function __construct()
    {
        $this->creator = app('migration.creator');
        $this->blueprintsMappers = collect();

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

            $this->blueprintsMappers->put($model->class, MapToBlueprint::make($modelProperties, new Blueprint($model->tableName)));
        });

        return $this;
    }

    public function buildMigrations(): self
    {
        $this->blueprintsMappers->each(function (MapToBlueprint $mapToBlueprint) {

            $isNewTable = !Schema::hasTable($mapToBlueprint->getBlueprint()->getTable());
            if ($isNewTable) {
                $migrationFile = $this->creator->create("create_{$mapToBlueprint->getBlueprint()->getTable()}_table", database_path('migrations'), $mapToBlueprint->getBlueprint()->getTable(), true);
                $this->generateMigrationFile($mapToBlueprint, $migrationFile);
                return $this;
            }
            $migrationFile = $this
                ->creator
                ->create("update_{$mapToBlueprint->getBlueprint()->getTable()}_table", database_path('migrations'), $mapToBlueprint->getBlueprint()->getTable(), false);



            $this->generateMigrationFile($mapToBlueprint, $migrationFile);

            // 2. Updating
            // 2.1. Get the current table columns.
            // 2.2. Get the new table columns.
            // 2.3. Compare the two columns.
            // 2.4. If there is a difference, update the migration file.


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

    public function getBlueprintsMappers(): Collection
    {
        return $this->blueprintsMappers;
    }

    private function generateMigrationFile(MapToBlueprint $mapToBlueprint, string $migrationFilePath): self
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
        return $this->blueprintsMappers->map(function (MapToBlueprint $mapToBlueprint) {
            return $mapToBlueprint->getBlueprint();
        });
    }
}

