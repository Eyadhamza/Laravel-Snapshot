<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Formatters;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;

class RemoveCommandFormatter extends Formatter
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

    private function formatName(): self
    {
        return $this;
    }

    private function formatStart(): self
    {
        $this->formattedCommand = "\$table->";

        return $this;
    }

    private function formatOperation(): self
    {
        return $this;
    }

    private function formatType(): self
    {
        return $this;
    }

    private function formatOptions(): self
    {
        return $this;
    }

    private function formatEnd(): self
    {
        return $this;
    }
}
