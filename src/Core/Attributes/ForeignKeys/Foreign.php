<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ColumnMapper;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Foreign extends ForeignKeyMapper
{

}
