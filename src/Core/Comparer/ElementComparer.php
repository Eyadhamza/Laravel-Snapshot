<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Comparer;

use Doctrine\DBAL\Schema\AbstractAsset;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
use Eyadhamza\LaravelEloquentMigration\Core\Mappers\ElementToCommandMapper;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class ElementComparer
{
    private Collection $addedElements;

    private Collection $removedElements;

    private Collection $modifiedElements;
    private Collection $modelElements;
    private Collection $doctrineElements;

    public function __construct()
    {
        $this->addedElements = new Collection;
        $this->removedElements = new Collection;
        $this->modifiedElements = new Collection;
    }

    public static function make(): ElementComparer
    {
        return new self();
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
            ->map(fn(AbstractAsset $doctrineElement) => ElementToCommandMapper::make($doctrineElement));

        return $this;
    }

    protected function removeOld(): self
    {
        $this->removedElements = $this->doctrineElements
            ->diffKeys($this->modelElements)
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

                $comparer = AttributeComparer::make($modelElementCommandMapper, $doctrineElementCommandMapper)->run();

                return $comparer->isChanged()
                    ? $modelElementCommandMapper->setChangedAttributes($comparer->getAllAttributes())
                    : null;
            })
            ->filter();

        return $this;
    }

    public function setModelElements(Collection $modelElements): ElementComparer
    {
        $this->modelElements = $modelElements;
        return $this;
    }

    public function setDoctrineElements(Collection $doctrineElements): ElementComparer
    {
        $this->doctrineElements = $doctrineElements;
        return $this;
    }

    public function getElements(): Collection
    {
        return collect([
            MigrationOperationEnum::Add->value => $this->addedElements,
            MigrationOperationEnum::Remove->value => $this->removedElements,
            MigrationOperationEnum::Remove->value => $this->modifiedElements,
        ]);
    }

}
