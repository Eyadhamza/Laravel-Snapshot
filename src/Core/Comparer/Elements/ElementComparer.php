<?php

namespace PiSpace\LaravelSnapshot\Core\Comparer\Elements;

use Doctrine\DBAL\Schema\AbstractAsset;
use Illuminate\Support\Collection;
use PiSpace\LaravelSnapshot\Core\Comparer\Attributes\AttributeComparer;
use PiSpace\LaravelSnapshot\Core\Constants\MigrationOperationEnum;
use PiSpace\LaravelSnapshot\Core\Mappers\ElementToCommandMapper;

class ElementComparer
{
    private Collection $addedElements;

    private Collection $removedElements;

    private Collection $modifiedElements;
    private Collection $modelElements;
    private Collection $doctrineElements;

    public function __construct(Collection $modelElements, Collection $doctrineElements)
    {
        $this->modelElements = $modelElements;
        $this->doctrineElements = $doctrineElements;
        $this->addedElements = new Collection;
        $this->removedElements = new Collection;
        $this->modifiedElements = new Collection;
    }

    public static function make(Collection $modelElements, Collection $doctrineElements): ElementComparer
    {
        return new self($modelElements, $doctrineElements);
    }

    public function run(): self
    {
        return $this
            ->addNew()
            ->removeOld()
            ->compareModified();
    }


    protected function addNew(): self
    {
        $this->addedElements = $this->modelElements
            ->diffKeys($this->doctrineElements)
            ->filter(fn(AbstractAsset $modelElement) => ! $this->isSpecialElement($modelElement))
            ->map(fn(AbstractAsset $doctrineElement) => ElementToCommandMapper::make($doctrineElement));

        return $this;
    }

    protected function removeOld(): self
    {
        $this->removedElements = $this->doctrineElements
            ->diffKeys($this->modelElements)
            ->filter(fn(AbstractAsset $modelElement) => ! $this->isSpecialElement($modelElement))
            ->map(fn(AbstractAsset $doctrineElement) => ElementToCommandMapper::make($doctrineElement));

        return $this;
    }

    protected function compareModified(): self
    {
        $this->modifiedElements = $this->modelElements
            ->intersectByKeys($this->doctrineElements)
            ->map(function (AbstractAsset $modelElement, $key) {
                $modelElementCommandMapper = ElementToCommandMapper::make($modelElement);
                $doctrineElementCommandMapper = ElementToCommandMapper::make($this->doctrineElements->get($key));

                $comparer = AttributeComparer::make($modelElementCommandMapper, $doctrineElementCommandMapper)
                    ->run();

                return $comparer->isChanged()
                    ? $modelElementCommandMapper->setChangedAttributes($comparer->getAllAttributes())
                    : null;
            })
            ->filter();

        return $this;
    }

    public function getElements(): Collection
    {
        return collect([
            MigrationOperationEnum::Add->value => $this->addedElements,
            MigrationOperationEnum::Remove->value => $this->removedElements,
            MigrationOperationEnum::Modify->value => $this->modifiedElements,
        ]);
    }

    private function isSpecialElement(AbstractAsset $modelElement): bool
    {
        return in_array($modelElement->getName(), ['id', 'timestamps','created_at','updated_at']);
    }

}
