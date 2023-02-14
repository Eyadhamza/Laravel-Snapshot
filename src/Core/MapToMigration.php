<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Spatie\ModelInfo\ModelInfo;

class MapToMigration
{

    /**
     * @var Collection<Model, Blueprint>
     */
    private Collection $modelBlueprints;

    public function __construct()
    {
        $this->modelBlueprints = collect();

        $this->mapModels(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));

    }

    public static function make(): self
    {
        return new self();
    }

    public function mapModels(Collection $models): self
    {
        foreach ($models as $model) {

            $attributes = $this
                ->getAttributesOfColumnType(new ReflectionClass($model->class));

            $modelProperties = $attributes
                ->map(fn(ReflectionAttribute $attribute) => $this->mapAttribute($attribute));

            $this->modelBlueprints->put($model->class, MapToBlueprint::make($modelProperties, new Blueprint($model->tableName)));

        }

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

    public function getModelBlueprints(): Collection
    {
        return $this->modelBlueprints;
    }

}
