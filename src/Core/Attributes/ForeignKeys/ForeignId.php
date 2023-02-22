<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys;

use Attribute;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ForeignId extends ForeignKeyMapper
{

}
