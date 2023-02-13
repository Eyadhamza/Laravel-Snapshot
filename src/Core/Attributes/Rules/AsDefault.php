<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules;

use Attribute;

#[Attribute]
class AsDefault
{
    public function __construct(
        private readonly ?string $value = null
    )
    {
    }
}
