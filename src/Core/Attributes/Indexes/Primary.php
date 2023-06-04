<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes;

use Attribute;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\AttributeEntity;

use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\ColumnMapper;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Primary extends IndexMapper
{
    public function setType(): AttributeEntity
    {
        $this->type = 'primary';

        return $this;
    }
}
