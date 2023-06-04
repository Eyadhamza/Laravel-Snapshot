<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes;

use Attribute;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\AttributeEntity;

use Doctrine\DBAL\Schema\UniqueConstraint;
use Illuminate\Database\Schema\Blueprint;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Unique extends AttributeEntity
{
    protected string|array $columns;
    protected string|null $algorithm;
    private UniqueConstraint $definition;

    public function __construct($columns, $algorithm = null)
    {
        parent::__construct("");
        $this->columns = $columns;
        $this->algorithm = $algorithm;
    }
    public function setDefinition(string $tableName): self
    {
        $indexKeyName = (new Blueprint($tableName))->unique($this->columns)->get('index');

        $this->definition = new UniqueConstraint(
            $indexKeyName,
            is_array($this->columns) ? $this->columns : [$this->columns],
        );

        $this->setName($indexKeyName);

        return $this;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }
    public function getDefinition(): UniqueConstraint
    {
        return $this->definition;
    }


    public function setOptions(array $options): AttributeEntity
    {
        $this->options = $options;

        return $this;
    }

    public function setType(): AttributeEntity
    {
        $this->type = 'unique';

        return $this;
    }
}
