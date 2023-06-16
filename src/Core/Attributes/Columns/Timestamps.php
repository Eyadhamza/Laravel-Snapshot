<?php

namespace PiSpace\LaravelSnapshot\Core\Attributes\Columns;

use Attribute;
use PiSpace\LaravelSnapshot\Core\Attributes\AttributeEntity;

use Illuminate\Database\Schema\Blueprint;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Timestamps extends ColumnMapper
{
    public function __construct()
    {
        parent::__construct('timestamps');
    }

    public function setType(): AttributeEntity
    {
        $this->type = 'timestamps';
        return $this;
    }
}
