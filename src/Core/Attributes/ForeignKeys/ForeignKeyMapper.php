<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys;

use Attribute;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Rule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignIdColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ForeignKeyMapper extends AttributeEntity
{
    protected string|array $columns;

    protected Fluent $definition;

    public function __construct($columns, $rules = [])
    {
        parent::__construct("", $rules);
        $this->columns = $columns;
    }
    public function setDefinition(string $tableName): self
    {
        $this->columns = is_array($this->columns) ? $this->columns : [$this->columns];
        $foreignKeyName = (new Blueprint($tableName))->foreign($this->columns)->get('index');
        $this->definition = new ForeignKeyDefinition(array_merge([
            'columns' => $this->columns,
            'name' => $foreignKeyName,
            'type' => 'foreignId',
            'unsigned' => true,
        ], $this->rules));

        $this->setName($this->columns[0]);

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
}
