<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns;

use Attribute;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class AsString extends ColumnMapper
{
    const DEFAULT_LENGTH = 255;

    public function __construct(string $name = null, array $rules = [])
    {
        $rules = array_merge(['length' => self::DEFAULT_LENGTH], $rules);
        parent::__construct($name, $rules);
    }
}
