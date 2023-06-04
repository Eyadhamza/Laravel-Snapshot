<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns;

use Attribute;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\AttributeEntity;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class AsString extends ColumnMapper
{
    const DEFAULT_LENGTH = 255;

    public function __construct(string $name = null, array $rules = [])
    {
        $rules = array_merge(['length' => self::DEFAULT_LENGTH], $rules);
        parent::__construct($name, $rules);
    }

    public function setDefinition(string $tableName): ColumnMapper
    {
        parent::setDefinition($tableName);

        $this->definition->setPlatformOptions([
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        return $this;
    }

    public function setType(): AttributeEntity
    {
        $this->type = 'string';

        return $this;
    }
}
