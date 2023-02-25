<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ColumnMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Unique extends IndexMapper
{
    public function setDefinition(string $tableName): self
    {
        $this->definition = (new Blueprint($tableName))->unique($this->columns);
        $this->setName($this->definition->get('index'));

        return $this;
    }
}
