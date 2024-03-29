<?php

namespace PiSpace\LaravelSnapshot\Core\Attributes\Columns;

use Attribute;
use PiSpace\LaravelSnapshot\Core\Attributes\AttributeEntity;


;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Id extends ColumnMapper
{
    public function __construct(string $name = null, array $rules = [])
    {
        $rules = array_merge(['length' => null], $rules);
        parent::__construct($name ?? 'id', $rules);
    }

    public function setDefinition(string $tableName): ColumnMapper
    {
        parent::setDefinition($tableName);

        $this->definition
            ->setNotnull(true)
            ->setUnsigned(true)
            ->setAutoincrement(true);

        return $this;
    }

    public function setType(): AttributeEntity
    {
        $this->type = 'id';

        return $this;
    }
}
