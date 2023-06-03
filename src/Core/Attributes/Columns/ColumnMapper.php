<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns;

use Attribute;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Types\StringType;
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
    private Column $definition;

    public function __construct(string $name = null, array $rules = [])
    {
        parent::__construct($name, $rules);
    }
    public function setDefinition(string $tableName): self
    {
        $this->definition = new Column(
            $this->getName(),
            new StringType,
        );

        return $this;
    }

    public function getDefinition(): Column
    {
        return $this->definition;
    }
}
