<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns;

use Attribute;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys\ForeignKeyMapper;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Rule;
use Eyadhamza\LaravelAutoMigration\Core\Rules;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;
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
            ->addColumn($this->getType(), $this->getName(), $this->getRules());

        return $this;
    }
}
