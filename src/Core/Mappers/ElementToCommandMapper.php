<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers;

use Doctrine\DBAL\Schema\AbstractAsset;
use Illuminate\Support\Collection;

class ElementToCommandMapper
{
    private AbstractAsset $element;
    private Collection $changedAttributes;
    private string $elementType;

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

    public function getChangedAttributes(): Collection
    {
        return $this->changedAttributes;
    }

    public function setElementType(string $elementType): ElementToCommandMapper
    {
        $this->elementType = $elementType;
        return $this;
    }

    public function getElementType(): string
    {
        return $this->elementType;
    }

    public function getDefinition(): AbstractAsset
    {
        return $this->element;
    }

    public function getName(): string
    {
        return $this->element->getName();
    }

    public function toArray()
    {
        return $this->element->toArray();
    }
}
