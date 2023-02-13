<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules;

use Attribute;
use Illuminate\Support\Collection;
use ReflectionAttribute;

class Rule
{
    private string $name;

    private array $arguments;

    public function __construct(string $name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public static function map(Collection $rules): Collection
    {
       return $rules->map(function (ReflectionAttribute $rule) {
            return new self($rule->getName(), $rule->getArguments());
        });
    }
}
