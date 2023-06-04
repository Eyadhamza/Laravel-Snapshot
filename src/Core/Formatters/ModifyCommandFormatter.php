<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Formatters;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;

class ModifyCommandFormatter extends Formatter
{

    public function format() : string
    {
        $this
            ->formatStart()
            ->formatName()
            ->formatOperation()
            ->formatType()
            ->formatOptions()
            ->formatEnd();


        return $this->formattedCommand;
    }

    private function formatName()
    {
        return $this;
    }

    private function formatStart(): self
    {
        $this->formattedCommand = "\$table->";

        return $this;
    }

    private function formatOperation()
    {
        return $this;
    }

    private function formatType()
    {
        return $this;
    }

    private function formatOptions()
    {
        return $this;
    }

    private function formatEnd()
    {
        return $this;
    }
}
