<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes;

use Attribute;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\BlueprintAttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Rules;
use Eyadhamza\LaravelAutoMigration\Core\Constants\AttributeToColumn;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;


#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class BlueprintIndexBuilder extends BlueprintAttributeEntity
{
    private string|array $columns;
    private string|null $algorithm;

    public function __construct($columns, $name = null, $algorithm = null)
    {
        parent::__construct($name);
        $this->columns = $columns;
        $this->algorithm = $algorithm;
    }

    public static function make(BlueprintIndexBuilder $modelProperty): self
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

}
