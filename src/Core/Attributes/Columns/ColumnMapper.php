<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Rules;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;
use Illuminate\Database\Schema\Blueprint;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ColumnMapper extends AttributeEntity
{

    public function __construct(string $name, array $rules = [])
    {
        parent::__construct($name, $rules);
        $this->name = $name;
        $this->rules = $rules;
    }

    public static function make(ColumnMapper $modelProperty): ColumnMapper
    {
        return new self($modelProperty->getName(), $modelProperty->getRules());
    }

}
