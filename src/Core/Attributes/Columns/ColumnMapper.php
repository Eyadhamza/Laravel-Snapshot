<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns;

use Attribute;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\AttributeEntity;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\StringType;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\ColumnOption;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
abstract class ColumnMapper extends AttributeEntity
{
    protected Column $definition;

    public function __construct(string $name = null, array $rules = [])
    {
        parent::__construct($name, $rules);
    }

    public function setDefinition(string $tableName): self
    {
        $this->definition = new Column(
            $this->getName(),
            new StringType,
            $this->getOptions(),
        );

        return $this;
    }

    public function getDefinition(): Column
    {
        return $this->definition;
    }

    public function setOptions(array $options): AttributeEntity
    {
        $this->options = array_merge([
            'length' => ColumnOption::DEFAULT_LENGTH,
        ], ColumnOption::map($options));
        return $this;
    }

}
