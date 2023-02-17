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


    protected Application $laravel;

    protected MigrationCreator $creator;

    protected Composer $composer;

    /**
     * @var Collection<Model, Blueprint>
     */
    private Collection $blueprintsMappers;

    public function __construct()
    {
        // get the connection instance

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

            $newBlueprint = $mapToBlueprint->getBlueprint();
            $table = $newBlueprint->getTable();

//            if (!Schema::hasTable($table)) {
//                $migrationFile = $this->creator->create("create_{$table}_table", database_path('migrations'), $table, true);
//                $this->generateMigrationFile($mapToBlueprint, $migrationFile);
//                return $this;
//            }
//            $migrationFile = $this
//                ->creator
//                ->create("update_{$table}_table", database_path('migrations'), $table, false);
            $tableName = 'books';
            $tableDetails = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableDetails($tableName);
            $blueprintForCurrentTable = new Blueprint($tableName, function ($blueprint) use ($tableDetails){
                foreach ($tableDetails->getColumns() as $column) {
                    $columnDefinition = $blueprint->{$column->getType()->getName()}($column->getName());
                    $columnDefinition->nullable(!$column->getNotnull());
                    if ($column->getUnsigned()) {
                        $columnDefinition->unsigned();
                    }
                    if ($column->getAutoincrement()) {
                        $columnDefinition->autoIncrement();
                    }
                    if ($column->getLength() !== null) {
                        $columnDefinition->length($column->getLength());
                    }
                    if ($column->getDefault() !== null) {
                        $columnDefinition->default($column->getDefault());
                    }
                }
                foreach ($tableDetails->getIndexes() as $index) {
                    $indexDefinition = $blueprint->index($index->getColumns(), $index->getName());
                    if ($index->isPrimary()) {
                        $indexDefinition->primary();
                    }
                    if ($index->isUnique()) {
                        $indexDefinition->unique();
                    }
                }
                foreach ($tableDetails->getForeignKeys() as $foreignKey) {
                    $foreignKeyDefinition = $blueprint->foreign($foreignKey->getLocalColumns())
                        ->references($foreignKey->getForeignColumns())
                        ->on($foreignKey->getForeignTableName())
                        ->name($foreignKey->getName());
                    if ($foreignKey->getOption('onUpdate') !== null) {
                        $foreignKeyDefinition->onUpdate($foreignKey->getOption('onUpdate'));
                    }
                    if ($foreignKey->getOption('onDelete') !== null) {
                        $foreignKeyDefinition->onDelete($foreignKey->getOption('onDelete'));
                    }
                }
            });
            dd($blueprintForCurrentTable);
            Schema::create($tableName, function ($blueprint) use ($tableDetails) {

            });
            // I want to get the blueprint of existing table in the database
            $newColumn = $newBlueprint->getColumns();
            dd($newColumn);
            $oldColumn = DB::connection()->getDoctrineColumn('books', 'title');
            dd($oldColumn);
            $oldBlueprint = $this;

            dd($oldBlueprint);
            dd($existingBlueprint); // getting close!
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

