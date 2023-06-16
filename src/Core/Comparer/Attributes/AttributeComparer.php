<?php

namespace PiSpace\LaravelSnapshot\Core\Comparer\Attributes;


use Illuminate\Support\Collection;
use PiSpace\LaravelSnapshot\Core\Constants\MigrationOperationEnum;
use PiSpace\LaravelSnapshot\Core\Mappers\ElementToCommandMapper;

class AttributeComparer
{
    private Collection $modelAttributes;
    private Collection $doctrineAttributes;
    protected Collection $addedAttributes;
    protected Collection $changedAttributesFromModel;
    protected Collection $changedAttributesFromDoctrine;
    protected Collection $deletedAttributes;

    private Collection $allAttributes;
    private bool $isChanged = false;

    public function __construct(ElementToCommandMapper $modelColumn, ElementToCommandMapper $doctrineColumn)
    {
        $this->modelAttributes = collect($modelColumn->toArray())->filter();
        $this->doctrineAttributes = collect($doctrineColumn->toArray())->filter();
        $this->addedAttributes = new Collection;
        $this->changedAttributesFromModel = new Collection;
        $this->changedAttributesFromDoctrine = new Collection;
        $this->deletedAttributes = new Collection;
        $this->allAttributes = new Collection;
    }

    public static function make(ElementToCommandMapper $modelColumn, ElementToCommandMapper $doctrineColumn): AttributeComparer
    {
        return new self($modelColumn, $doctrineColumn);
    }

    public function run(): self
    {
        return $this
            ->compareType()
            ->compareColumnsNames()
            ->compareAddedAttributes()
            ->compareChangedAttributesFromModel()
            ->compareChangedAttributesFromDoctrine()
            ->compareDeletedAttributes()
            ->wrapAttributes();
    }

    protected function compareAddedAttributes(): self
    {
        $this->addedAttributes = $this->modelAttributes
            ->diffKeys($this->doctrineAttributes)
            ->filter(fn($value, $key) => $value === null);

        return $this;
    }

    protected function compareChangedAttributesFromModel(): self
    {
        $this->changedAttributesFromModel = $this->modelAttributes
            ->diffAssoc($this->doctrineAttributes)
            ->diffKeys($this->deletedAttributes)
            ->filter(fn($value, $key) => $value === null);

        return $this;
    }

    protected function compareChangedAttributesFromDoctrine(): self
    {
        $this->changedAttributesFromDoctrine = $this->doctrineAttributes
            ->diffAssoc($this->modelAttributes)
            ->diffKeys($this->addedAttributes)
            ->filter(fn($value, $key) => $value === null);

        return $this;
    }

    protected function compareDeletedAttributes(): self
    {
        $this->deletedAttributes = $this->doctrineAttributes
            ->diffKeys($this->modelAttributes)
            ->filter(fn($value, $key) => $value === null);

        return $this;
    }

    private function wrapAttributes(): self
    {
        if (empty($this->addedAttributes) && empty($this->changedAttributesFromModel) && empty($this->changedAttributesFromDoctrine) && empty($this->deletedAttributes)) {
            return $this;
        }

        $this->allAttributes = collect([
            MigrationOperationEnum::Add->value => $this->addedAttributes,
            MigrationOperationEnum::Modify->value => [
                'fromModel' => $this->changedAttributesFromModel,
                'fromDoctrine' => $this->changedAttributesFromDoctrine,
            ],
            MigrationOperationEnum::Remove->value => $this->deletedAttributes,
        ]);

        return $this;
    }

    public function isChanged(): bool
    {
        return $this->allAttributes->flatten()->isNotEmpty();
    }

    public function getAllAttributes(): Collection
    {
        return $this->allAttributes;
    }

    private function compareType(): self
    {
        $this->modelAttributes->pull('type');

        $this->doctrineAttributes->pull('type');
        //TODO: compare type
        return $this;
    }

    private function compareColumnsNames(): self
    {
        $this->modelAttributes->pull('columns');
        $this->doctrineAttributes->pull('columns');
        //TODO: compare columns
        return $this;
    }
}
