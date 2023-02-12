<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
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
        ModelInfo::forAllModels()->each(function (ModelInfo $model) {
            $this->modelBlueprints[$model->class] = new Blueprint($model->class);
            $this->mapModel($model->class);
        });
    }

    public static function make(): self
    {
        return new self();
    }

    public function mapModel(string $model)
    {

        $properties = $this->setModelProperties(new ReflectionClass($model));

        $properties->each(fn(ReflectionProperty $property) => $this->mapProperty($property, $model));

        return $this;
    }

    public function setModelProperties(ReflectionClass $reflectionClass)
    {
        return collect($reflectionClass->getProperties())->filter(function ($property) {
            return $property->getAttributes('Eyadhamza\LaravelAutoMigration\Core\Attributes\Property');
        });
    }

    private function mapProperty(ReflectionProperty $property,$modelName)
    {
        $property = $property
            ->getAttributes('Eyadhamza\LaravelAutoMigration\Core\Attributes\Property')[0]
            ->newInstance()
            ->setPropertyName($property->getName())
            ->setPropertyType($property->getType()->getName());

        $this->setModelProperty($property, $modelName);
        return $this;
    }

    public function setModelProperty(Property $property, string $modelName): self
    {
        $rules = $property->getRules();
        $blueprint = $this->modelBlueprints[$modelName]->{$property->getPropertyType()}($property->getPropertyName());
        $allowedRules = Rule::getRules();

        foreach ($rules as $rule => $value) {
            if (is_int($rule)) {
                $rule = $value;
                $value = null;
            }
            if (! array_key_exists($rule, $allowedRules)){
                throw new \Exception("Rule {$rule} not found");
            }
            $blueprint->{$rule}($value);
        }
        return $this;
    }

}
