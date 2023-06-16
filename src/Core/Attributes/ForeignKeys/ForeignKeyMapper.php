<?php

namespace PiSpace\LaravelSnapshot\Core\Attributes\ForeignKeys;

use Attribute;
use PiSpace\LaravelSnapshot\Core\Attributes\AttributeEntity;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
abstract class ForeignKeyMapper extends AttributeEntity
{
    protected string|array $columns;

    protected ForeignKeyConstraint $definition;

    public function __construct($columns, $rules = [])
    {
        parent::__construct("", $rules);
        $this->columns = $columns;
    }
    public function setDefinition(string $tableName): self
    {

        $this->columns = is_array($this->columns) ? $this->columns : [$this->columns];
        $foreignKeyName = (new Blueprint($tableName))->foreign($this->columns);
        $foreignTable = Str::before(Str::singular($foreignKeyName->get('columns')[0]),'_') . 's';
        $this->definition = new ForeignKeyConstraint(
            ['id'],
            $foreignTable,
            $this->columns,
            $foreignKeyName->get('index'),
            $this->options
        );

        $this->setName($foreignKeyName->get('index'));

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

    public function getDefinition(): ForeignKeyConstraint
    {
        return $this->definition;
    }

    public function setOptions(array $options): AttributeEntity
    {
        foreach ($options as $value) {
            $this->options[$value] = true;
        }

        return $this;
    }
}
