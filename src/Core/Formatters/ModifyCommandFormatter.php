<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Formatters;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;

class ModifyCommandFormatter extends Formatter
{
    protected function formatOperation(): self
    {
        return $this;
    }

    protected function formatOptions(): self
    {
        return $this;
    }

    protected function formatEnd(): self
    {
        return $this;
    }

}
