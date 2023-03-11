<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns;

use Attribute;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignKeyMapper;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\Rule;
use Eyadhamza\LaravelEloquentMigration\Core\Rules;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\AttributeToColumn;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ColumnMapper extends AttributeEntity
{
    public function __construct(string $name = null, array $rules = [])
    {
        parent::__construct($name, $rules);
    }
    public function setDefinition(string $tableName): self
    {
        $this->definition = (new Blueprint($tableName))
            ->addColumn($this->getType(), $this->getName(), $this->rules);
        return $this;
    }
}
