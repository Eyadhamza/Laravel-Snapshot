<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\BlueprintColumnBuilder;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class BlueprintAttributeEntity
{
    protected mixed $name;
    protected ?array $rules;
    protected string $type;

    public function __construct(string $name = null, array $rules = [])
    {
        $this->name = $name;
        $this->rules = $rules;
    }

    public function getName(): mixed
    {
        return $this->name ?? null;
    }

    public function getRules(): ?array
    {
        return $this->rules;
    }
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = AttributeToColumn::map($type);

        return $this;
    }

}
