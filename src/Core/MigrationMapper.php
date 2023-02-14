<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Name;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Spatie\ModelInfo\ModelInfo;

class MigrationMapper
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
            $this->modelBlueprints[$model->class] = new Blueprint($model->tableName);

            $modelName = $model->class;
            $properties = $this
                ->getProperties(new ReflectionClass($modelName));

            $modelProperties = $properties
                ->map(fn(ReflectionProperty $property) => $this->mapProperty($property));

            $this->modelBlueprints[$model->class] = ModelToBlueprintMapper::make($modelProperties, $this->modelBlueprints[$modelName])->build();
        }

        dd($this->modelBlueprints);
        return $this;
    }

    public function getProperties(ReflectionClass $reflectionClass): Collection
    {
        return collect($reflectionClass->getProperties())->filter(function ($property) {
            return $property->getAttributes(Property::class);
        });
    }

    private function mapProperty(ReflectionProperty $property): Property
    {
        $attributes = collect($property->getAttributes());

        $rules = $attributes
            ->filter(fn(ReflectionAttribute $attribute) => is_subclass_of($attribute->getName(), Rule::class));

        $propertyType = $attributes
            ->filter(fn(ReflectionAttribute $attribute) => $attribute ->getName() === Property::class)
            ->first()
            ->getArguments()[0] ?? $property->getType()->getName();

        return $attributes
            ->filter(fn(ReflectionAttribute $attribute) => $attribute->getName() === Property::class)
            ->first()
            ->newInstance()
            ->setName($property->getName())
            ->setType($propertyType)
            ->setRules($rules);

    }

}
