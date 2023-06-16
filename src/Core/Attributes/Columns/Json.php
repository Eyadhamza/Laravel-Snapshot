<?php

namespace PiSpace\LaravelSnapshot\Core\Attributes\Columns;

use Attribute;
use PiSpace\LaravelSnapshot\Core\Attributes\AttributeEntity;
;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Json extends ColumnMapper
{
    public function setType(): AttributeEntity
    {
        $this->type = 'json';

        return $this;
    }
}
