<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Rules;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;
use Illuminate\Database\Schema\Blueprint;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class BlueprintIndexBuilder
{
    private string|array $columns;
    private string|null $name;
    private string|null $algorithm;

    public function __construct($columns, $name = null, $algorithm = null)
    {
        $this->columns = $columns;
        $this->name = $name;
        $this->algorithm = $algorithm;
    }

}
