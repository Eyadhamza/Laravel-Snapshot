<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ColumnMapper;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Rule;
use Eyadhamza\LaravelAutoMigration\Core\MigrationGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
abstract class AttributeEntity
{
    protected ?string $name;
    protected string $type;
    protected ?array $rules;
    protected Fluent $definition;
    public function __construct(string $name = null, array $rules = [])
    {
        $this->name = $name;
        $this->setRules($rules);
    }

    public function getName(): ?string
    {
        return $this->name ?? null;
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
    abstract public function setDefinition(string $tableName): self;
    public function getRules(): ?array
    {
        return $this->rules;
    }

    public function setRules(array $rules): self
    {
        $this->rules = Rule::map($rules);

        return $this;
    }

    public function getDefinition(): Fluent
    {
        return $this->definition;
    }

}
