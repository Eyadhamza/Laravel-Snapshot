<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Rule;
use Illuminate\Support\Collection;

#[Attribute]
class Property
{
    private string $name;
    private Collection $rules;

    public function __construct(
        private  ?string $type = null,
    )
    {}
    public function setType(string $type): self
    {
        $this->type = $type;
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
        $this->rules = Rule::map($rules);

        return $this;
    }
}
