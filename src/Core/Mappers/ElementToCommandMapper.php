<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers;

use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Support\Collection;

class ElementToCommandMapper
{
    private AbstractAsset $element;
    private Collection $changedAttributes;

    public function __construct(AbstractAsset $element, Collection $changedAttributes = null)
    {
        $this->element = $element;
        $this->changedAttributes = $changedAttributes ?? new Collection;
    }

    public static function make(AbstractAsset $modelElement, Collection $changedAttributes = null): ElementToCommandMapper
    {
        return new self($modelElement, $changedAttributes);
    }

    public static function collection(Collection $elements): Collection
    {
        return $elements->map(fn($element) => ElementToCommandMapper::make($element));
    }


    public function getElementType(): string
    {
        return $this->getDefinition()->laravelType;
    }

    public function getDefinition(): AbstractAsset
    {
        return $this->element;
    }

    public function getName(): string|array|null
    {
        return match (get_class($this->element)) {
            Column::class => $this->element->getName(),
            Index::class => $this->element->getColumns(),
            ForeignKeyConstraint::class => $this->element->getForeignColumns(),
            default => null,
        };
    }

    public function toArray()
    {
        return match (get_class($this->element)) {
            Column::class => $this->element->toArray(),
            Index::class, ForeignKeyConstraint::class => $this->element->getOptions(),
            default => [],
        };
    }
}
