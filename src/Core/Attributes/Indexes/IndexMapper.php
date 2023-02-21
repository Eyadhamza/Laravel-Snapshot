<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Rules;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class IndexMapper extends AttributeEntity
{
    private string|array $columns;
    private string|null $algorithm;

    public function __construct($columns, $name = null, $algorithm = null)
    {
        parent::__construct($name);
        $this->columns = $columns;
        $this->algorithm = $algorithm;
    }

    public static function make(IndexMapper $modelProperty): self
    {

        return new self($modelProperty->getColumns(), $modelProperty->getName(), $modelProperty->getAlgorithm());
    }

    public function getName(): mixed
    {
        return $this->columns;
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
