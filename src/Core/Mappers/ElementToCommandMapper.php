<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers;

use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Support\Collection;

class ElementToCommandMapper
{
    private string $elementDefinitionName;
    private AbstractAsset $element;
    private Collection $changedAttributes;

    public function __construct(AbstractAsset $element)
    {
        $this->element = $element;
        $this->elementDefinitionName = get_class($element);
    }

    public static function make(AbstractAsset $modelElement, Collection $changedAttributes = null): ElementToCommandMapper
    {
        return new self($modelElement, $changedAttributes);
    }

    public static function collection(Collection $elements): Collection
    {
        return $elements->map(fn($element) => ElementToCommandMapper::make($element));
    }


    public function getType(): string
    {
        return $this->getDefinition()->laravelType;
    }

    public function getDefinition(): AbstractAsset
    {
        return $this->element;
    }

    public function getName(): string|array|null
    {
        return match ($this->elementDefinitionName) {
            Column::class => $this->element->getName(),
            Index::class => $this->element->getColumns(),
            ForeignKeyConstraint::class => $this->element->getForeignColumns(),
            default => null,
        };
    }

    public function toArray()
    {
        return match ($this->elementDefinitionName) {
            Column::class => $this->element->toArray(),
            Index::class, ForeignKeyConstraint::class => $this->element->getOptions(),
            default => [],
        };
    }

    public function setChangedAttributes(Collection $changedAttributes): self
    {
        $this->changedAttributes = $changedAttributes;

        return $this;
    }
}
