<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes;

use Attribute;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\ColumnMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Fluent;

;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Unique extends IndexMapper
{
    public function setDefinition(string $tableName): self
    {
        $indexKeyName = (new Blueprint($tableName))->unique($this->columns)->get('index');
        $this->definition = new IndexDefinition([
            'columns' => is_array($this->columns) ? $this->columns : [$this->columns],
            'name' => $indexKeyName,
            'unique' => true,
        ]);

        $this->setName($indexKeyName);

        return $this;
    }
}
