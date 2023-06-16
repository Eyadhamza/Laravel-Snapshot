<?php

namespace PiSpace\LaravelSnapshot\Core\Formatters;

use PiSpace\LaravelSnapshot\Core\Constants\MigrationOperationEnum;

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
