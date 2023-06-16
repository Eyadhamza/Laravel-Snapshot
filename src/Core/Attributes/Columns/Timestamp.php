<?php

namespace PiSpace\LaravelSnapshot\Core\Attributes\Columns;

use Attribute;
use PiSpace\LaravelSnapshot\Core\Attributes\AttributeEntity;
;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Timestamp extends ColumnMapper
{

    public function setType(): AttributeEntity
    {
        $this->type = 'timestamp';

        return $this;
    }
}
