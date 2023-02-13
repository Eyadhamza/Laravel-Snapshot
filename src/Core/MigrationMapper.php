<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Name;
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

        $properties = $this->setModelProperties(new ReflectionClass($modelName));

        $properties->each(fn(ReflectionProperty $property) => $this->mapProperty($property, $modelName));

        return $this;
    }

    public function setModelProperties(ReflectionClass $reflectionClass): Collection
    {
        return collect($reflectionClass->getProperties())->filter(function ($property) {
            return $property->getAttributes('Eyadhamza\LaravelAutoMigration\Core\Attributes\Property');
        });
    }

    private function mapProperty(ReflectionProperty $property, $modelName): self
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

        $blueprint = $this->buildColumn($property, $modelName);
        $allowedRules = Name::getRules();

        foreach ($rules as $rule => $value) {
            if (is_int($rule)) {
                $rule = $value;
                $blueprint->{$rule}();
                return $this;
            }

            if (!array_key_exists($rule, $allowedRules)) {
                throw new \Exception("Name {$rule} not found");
            }

            $blueprint->{$rule}($value);

        }
        return $this;
    }

    private function buildColumn(Property $property, string $modelName)
    {
        $blueprint = $this->modelBlueprints[$modelName];

        $columnType = $this->mapToColumn($property->getPropertyType());

        $columnName = $property->getPropertyName();

        return $blueprint->$columnType($columnName);
    }

    private function mapToColumn(string $propertyType): string
    {
        return match ($propertyType) {
            'int' => 'integer',
            'string' => 'string',
        };
    }

}
