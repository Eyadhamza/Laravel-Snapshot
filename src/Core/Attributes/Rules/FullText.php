<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules;

use Attribute;

#[Attribute]
class FullText extends Rule
{
    public function __construct(
        private readonly ?string $value = null
    )
    {
    }
}
