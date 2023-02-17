<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Doctrine\DBAL\Schema\Table;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

abstract class BlueprintBuilder
{
    protected Blueprint $blueprint;
    protected Collection $mappedColumns;

    public function __construct(Blueprint $blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public abstract function build(): self;

    public function getMapped(): Collection
    {
        return $this->mappedColumns;
    }

    public function getBlueprint(): Blueprint
    {
        return $this->blueprint;
    }

    public function getTable(): string
    {
        return $this->blueprint->getTable();
    }
}
