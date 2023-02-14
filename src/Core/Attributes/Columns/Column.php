<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\MapToBlueprintColumn;
use Eyadhamza\LaravelAutoMigration\Core\MapToBlueprintRule;
use Illuminate\Support\Collection;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Column
{

    public function __construct(
        private string $name,
        private ?array $rules = null,
    )
    {}
    public function setType(string $type): self
    {
        $this->type = MapToBlueprintColumn::map($type);
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function setRules(Collection $rules): self
    {
        $this->rules = MapToBlueprintRule::map($rules);

        return $this;
    }
}
