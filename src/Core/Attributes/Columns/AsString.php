<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns;

use Attribute;;

#[Attribute]
class AsString extends Column
{
    public function __construct(
        private ?string $value = null
    )
    {
    }
    public function getValue(): string
    {
        return $this->value;
    }
}
