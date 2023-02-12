<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes;
use Attribute;
use Illuminate\Database\Schema\Blueprint;
use ReflectionClass;

#[Attribute]
class Property
{
    private string $propertyType;
    private string $propertyName;

    public function __construct(
        private array|string $rules,
    ){}

    public function setPropertyType(string $propertyType): Property
    {
        $this->propertyType = $propertyType;
        return $this;
    }

    public function setPropertyName(string $propertyName): Property
    {
        $this->propertyName = $propertyName;
        return $this;
    }

    public function getRules(): array|string
    {
        return is_array($this->rules) ? $this->rules : [$this->rules];
    }

    public function getPropertyType(): string
    {
        return $this->propertyType;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
