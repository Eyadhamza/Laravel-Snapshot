<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns;

use Attribute;;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Mappers\Rule;
use Illuminate\Support\Collection;

#[Attribute]
#[Attribute]
class Column extends Column
{
    private AsString $name;
    private Collection $rules;

    public function __construct(
        private  ?AsString $type = null,
    )
    {}
    public function setType(AsString $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setName(AsString $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): AsString
    {
        return $this->type;
    }

    public function getName(): AsString
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
