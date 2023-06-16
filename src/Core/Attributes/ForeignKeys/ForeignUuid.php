<?php

namespace PiSpace\LaravelSnapshot\Core\Attributes\ForeignKeys;

use Attribute;
use PiSpace\LaravelSnapshot\Core\Attributes\AttributeEntity;

use PiSpace\LaravelSnapshot\Core\Attributes\Columns\ColumnMapper;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ForeignUuid extends ForeignKeyMapper
{
    public function setType(): AttributeEntity
    {
        $this->type = 'foreignUuid';

        return $this;
    }
}
