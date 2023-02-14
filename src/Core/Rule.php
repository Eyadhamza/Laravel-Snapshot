<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\After;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\AsDefault;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Change;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Comment;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\First;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\FullText;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Index;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Nullable;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Primary;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Required;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Unique;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Unsigned;
use Illuminate\Support\Collection;
use ReflectionAttribute;

class Rule
{
    private static array $rules = [
        After::class => 'after',
        AsDefault::class => 'default',
        Nullable::class => 'nullable',
        Unique::class => 'unique',
        Unsigned::class => 'unsigned',
        Required::class => '',
        Primary::class => 'primary',
        First::class => 'first',
        Change::class => 'change',
        Comment::class => 'comment',
        Index::class => 'index',
        FullText::class => 'fullText',
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
