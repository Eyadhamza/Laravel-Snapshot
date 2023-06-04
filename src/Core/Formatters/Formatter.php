<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Formatters;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;

abstract class Formatter
{
    protected string $formattedCommand;
    protected string|array|null $name;
    protected MigrationOperationEnum $operation;
    protected string $type;
    protected array $options;

    public function setNameOrNames(array|string|null $elementName): static
    {
        $this->name = $elementName;
        return $this;
    }

    public function setOperation(MigrationOperationEnum $operation): static
    {
        $this->operation = $operation;
        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function setOptions($options): static
    {
        $this->options = $options;
        return $this;
    }

    abstract public function format(): string;

}
