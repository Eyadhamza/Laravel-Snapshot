<?php

namespace PiSpace\LaravelSnapshot\Core\Attributes\Columns;

use Attribute;
use PiSpace\LaravelSnapshot\Core\Attributes\AttributeEntity;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\StringType;
use PiSpace\LaravelSnapshot\Core\Constants\ColumnOption;


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
