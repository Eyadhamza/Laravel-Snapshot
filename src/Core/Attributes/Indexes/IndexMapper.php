<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class IndexMapper extends AttributeEntity
{
    protected string|array $columns;
    protected string|null $algorithm;
    protected Fluent $definition;

    public function __construct($columns, $algorithm = null)
    {
        parent::__construct("");
        $this->columns = $columns;
        $this->algorithm = $algorithm;
    }

    public function setDefinition(string $tableName): self
    {
        $this->columns = is_array($this->columns) ? $this->columns : [$this->columns];
        $this->definition = (new Blueprint($tableName))->index($this->columns);
        $this->setName($this->definition->get('index'));
        return $this;
    }
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }
    public static function make(IndexMapper $modelProperty): self
    {
        return new self($modelProperty->getColumns(), $modelProperty->getAlgorithm());
    }
    public function getName(): mixed
    {
        return $this->name;
    }
    public function getColumns(): array|string
    {
        return $this->columns;
    }

    private function getAlgorithm()
    {
        return $this->algorithm;
    }
    public function get($key)
    {
        $method = 'get' . Str::camel($key);
        return $this->$method();
    }
}
