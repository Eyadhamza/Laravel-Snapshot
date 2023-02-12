<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionProperty;
use Spatie\ModelInfo\ModelFinder;
use Spatie\ModelInfo\ModelInfo;

class MigrationMapper
{
    private Collection $models;
    private ReflectionClass $reflectionClass;
    private Collection $properties;

    /**

     * @var Collection<Model, Blueprint>
     */
    private Collection $modelBlueprints;
    public function __construct()
    {
        $this->models = ModelInfo::forAllModels();
        $this->modelBlueprints = collect();
        $this->models->each(function (ModelInfo $model) {
            $this->reflectionClass = new ReflectionClass($model->class);
            $this->modelBlueprints[$model->class] = new Blueprint($model->tableName);
        });

        $this->mapModels();
    }

    public static function make(): self
    {
        return new self();
    }

    public function mapModels()
    {

        $this->setModelProperties();

        $this->properties
            ->each(fn(ReflectionProperty $property) => $this->mapProperty($property));

        dd($this->modelBlueprints);

    }

    public function setModelProperties()
    {
        $this->properties = collect($this->reflectionClass->getProperties())->filter(function ($property) {
            return $property->getAttributes('App\Core\Property');
        });

        return $this;
    }

    private function mapProperty(ReflectionProperty $property)
    {
        $property = $property
            ->getAttributes('App\Core\Property')[0]
            ->newInstance()
            ->setBlueprint($this->modelBlueprints[$this->reflectionClass->name])
            ->setPropertyName($property->getName())
            ->setPropertyType($property->getType()->getName());

        $this->setModelProperty($property);

        return $this;
    }

    public function setModelProperty(Property $property): self
    {
        $rules = $property->getRules();
        $blueprint = $this->modelBlueprints[$this->reflectionClass->name]->{$property->getPropertyType()}($property->getPropertyName());
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
