<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys;

use Attribute;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignIdColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ForeignId extends ForeignKeyMapper
{
}
