<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Type;

#[Attribute]
class Property
{
    public function __construct(
        private readonly ?string $type = null,
    )
    {
    }
}
