<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\BlueprintColumnBuilder;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Index extends BlueprintIndexBuilder
{

}