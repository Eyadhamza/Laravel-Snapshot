<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;
use Illuminate\Support\Str;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ForeignKeyMapper extends AttributeEntity
{

}
