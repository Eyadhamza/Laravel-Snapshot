<?php

namespace PiSpace\LaravelSnapshot\Core\Attributes\ForeignKeys;

use Attribute;
use PiSpace\LaravelSnapshot\Core\Attributes\AttributeEntity;



#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class ForeignId extends ForeignKeyMapper
{
    public function setType(): AttributeEntity
    {
        $this->type = 'foreignId';

        return $this;
    }

}
