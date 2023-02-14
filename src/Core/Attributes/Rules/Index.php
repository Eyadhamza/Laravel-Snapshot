<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules;

use Attribute;

#[Attribute]
class Index extends Rule
{
    public function __construct(
        private readonly ?string $value = null
    )
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
