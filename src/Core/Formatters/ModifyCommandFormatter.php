<?php

namespace PiSpace\LaravelSnapshot\Core\Formatters;

use PiSpace\LaravelSnapshot\Core\Constants\MigrationOperationEnum;

class ModifyCommandFormatter extends Formatter
{
    protected function formatEnd(): self
    {
        return $this->append('->change();');
    }

}
