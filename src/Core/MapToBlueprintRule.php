<?php

namespace Eyadhamza\LaravelAutoMigration\Core;


use Eyadhamza\LaravelAutoMigration\Core\Constants\Rule;
use Illuminate\Support\Collection;
use ReflectionAttribute;

class MapToBlueprintRule
{
    private static array $rules = [
        Rule::AFTER ,
        Rule::DEFAULT ,
        Rule::NULLABLE ,
        Rule::UNIQUE ,
        Rule::UNSIGNED,
        Rule::PRIMARY,
        Rule::FIRST,
        Rule::CHANGE,
        Rule::COMMENT,
        Rule::INDEX,
        Rule::FULL_TEXT,
        Rule::AUTO_ICREMENT,
    ];
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

            if (!array_key_exists($rule->getName(), self::$rules)) {
                throw new \Exception("Name {$rule} not found");
            }

           return new self(self::$rules[$rule->getName()], $rule->getArguments());
        });
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
