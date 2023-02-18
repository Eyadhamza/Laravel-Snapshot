<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Rules;
use Eyadhamza\LaravelAutoMigration\Core\Constants\BlueprintColumns;
use Illuminate\Database\Schema\Blueprint;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class BlueprintColumnBuilder
{
    private string $name;
    private array $rules;
    private string $type;

    public function __construct(string $name, array $rules = [])
    {
        $this->name = $name;
        $this->rules = $rules;
    }

    public static function make(BlueprintColumnBuilder $modelProperty): BlueprintColumnBuilder
    {
        return new self($modelProperty->getName(), $modelProperty->getRules());
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = BlueprintColumns::map($type);

        return $this;
    }

}
