<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Rule;
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

        ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path())->each(function (ModelInfo $model) {
            $this->modelBlueprints[$model->class] = new Blueprint($model->tableName);
            $this->mapModel($model->class);
        });
    }

    public static function make(): self
    {
        return new self();
    }

    public function mapModel(string $modelName): self
    {

        $properties = $this
            ->setModelProperties(new ReflectionClass($modelName));

        $modelProperties = $properties
            ->map(fn(ReflectionProperty $property) => $this->mapProperty($property));

        $this->modelBlueprints[$modelName] = ModelToBlueprintMapper::make($modelProperties)->build();

        return $this;
    }

    public function setModelProperties(ReflectionClass $reflectionClass): Collection
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

        return $attributes
            ->filter(fn(ReflectionAttribute $attribute) => $attribute->getName() === Property::class)
            ->first()
            ->newInstance()
            ->setName($property->getName())
            ->setType($property->getType()->getName())
            ->setRules($rules);

    }

}
