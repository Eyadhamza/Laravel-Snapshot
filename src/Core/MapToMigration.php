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
    private Collection $modelBlueprints;

    public function __construct()
    {
        $this->creator = app('migration.creator');
        $this->modelBlueprints = collect();

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

            $this->modelBlueprints->put($model->class, MapToBlueprint::make($modelProperties, new Blueprint($model->tableName)));
        });

        return $this;
    }

    public function buildMigrations()
    {
        $this->modelBlueprints->each(function (MapToBlueprint $mapToBlueprint) {

            $migrationFile = $this->creator->create("create_{$mapToBlueprint->getBlueprint()->getTable()}_table", database_path('migrations'), $mapToBlueprint->getBlueprint()->getTable(), true);

            $this->generateMigrationFile($mapToBlueprint, $migrationFile);


            // 2. Updating
            // 2.1. Get the current table columns.
            // 2.2. Get the new table columns.
            // 2.3. Compare the two columns.
            // 2.4. If there is a difference, update the migration file.


        });

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

    public function getModelBlueprints(): Collection
    {
        return $this->modelBlueprints;
    }

    private function generateMigrationFile(MapToBlueprint $mapToBlueprint, string $migrationFilePath)
    {
        $migrationFile = file_get_contents($migrationFilePath);

        $migrationFile = Str::replace("public function up()
    {
        Schema::create('{$mapToBlueprint->getBlueprint()->getTable()}', function (Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
        });
    }"
            , "public function up(){
        Schema::create('{$mapToBlueprint->getBlueprint()->getTable()}', function (Blueprint \$table) {
            {$mapToBlueprint->getMappedColumns()->join("\n \t \t \t")}
        });
     }", $migrationFile);

        file_put_contents($migrationFilePath, $migrationFile);
    }

    private function getMappedColumns(Blueprint $blueprint)
    {
        $columns = collect($blueprint->getColumns());

        return $columns->map(function (ColumnDefinition $column) {
            $columnDefinition = "\$table->{$column->type}('$column->name')";
            dump($column->getAttributes());
            $columnRules = collect($column->getAttributes())
                ->skip(2)
                ->map(function ($exist, $attribute) {
                    return $exist ? "->{$attribute}()" : '';
                })
                ->implode('');
            return $columnDefinition . $columnRules . ';';
        })->join(PHP_EOL);
    }

}
