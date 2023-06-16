<?php

namespace PiSpace\LaravelSnapshot\Core\Generators;

use Illuminate\Support\Collection;

abstract class Generator
{
    protected Collection $generated;
    protected string $tableName;

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
        $this->generated = new Collection;
    }

    public static function make(string $tableName): static
    {
        return new static($tableName);
    }

    public function getGenerated(): Collection
    {
        return $this->generated->flatten()->filter()->values();
    }

    public function setGeneratedCommands(Collection $generated): Generator
    {
        $this->generated = $generated;
        return $this;
    }

}
