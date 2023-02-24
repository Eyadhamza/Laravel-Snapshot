<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignIdColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Str;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ForeignKeyMapper extends AttributeEntity
{
    protected string|array $columns;
    protected ForeignKeyDefinition|ForeignIdColumnDefinition $definition;

    public function __construct($columns, $rules = [])
    {
        parent::__construct("", $rules);
        $this->columns = $columns;
    }
    public function setDefinition(string $tableName): self
    {
        $this->definition = (new Blueprint($tableName))->foreign($this->columns);
        $this->setName($this->definition->get('index'));

        return $this;
    }
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColumns(): array|string
    {
        return $this->columns;
    }

    public function getDefinition(): ForeignKeyDefinition
    {
        return $this->definition;
    }
}
