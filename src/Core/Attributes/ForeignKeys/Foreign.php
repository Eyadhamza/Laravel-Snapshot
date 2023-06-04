<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys;

use Attribute;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\AttributeEntity;

use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\ColumnMapper;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Foreign extends ForeignKeyMapper
{
    public function setType(): AttributeEntity
    {
        $this->type = 'foreign';

        return $this;
    }
}
