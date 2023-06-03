<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Comparer;

use Illuminate\Support\Arr;

class AttributeComparer
{

    private array $modelAttributes;
    private array $doctrineAttributes;
    protected array $addedAttributes = [];
    protected array $changedAttributesFromModel = [];
    protected array $changedAttributesFromDoctrine = [];
    protected array $deletedAttributes = [];
    private bool $isChanged = false;
    private array $allAttributes = [];

    public function __construct(array $modelAttributes, array $doctrineAttributes)
    {
        $this->modelAttributes = $modelAttributes;
        $this->doctrineAttributes = $doctrineAttributes;
    }

    public static function make(array $modelAttributes, array $doctrineAttributes): AttributeComparer
    {
        return new self($modelAttributes, $doctrineAttributes);
    }

    public function run(): self
    {
        return $this
            ->compareAddedAttributes()
            ->compareChangedAttributesFromModel()
            ->compareChangedAttributesFromDoctrine()
            ->compareDeletedAttributes()
            ->wrapAttributes();
    }

    protected function compareAddedAttributes(): self
    {
        $this->addedAttributes = array_diff_key($this->modelAttributes, $this->doctrineAttributes);

        return $this;
    }

    protected function compareChangedAttributesFromModel(): self
    {
        $this->changedAttributesFromModel = array_diff_assoc(
            Arr::except($this->modelAttributes, 'columns'),
            Arr::except($this->doctrineAttributes, 'columns')
        );
        return $this;
    }

    protected function compareChangedAttributesFromDoctrine(): self
    {
        $this->changedAttributesFromDoctrine = array_diff_assoc(
            Arr::except($this->doctrineAttributes, 'columns'),
            Arr::except($this->modelAttributes, 'columns')
        );
        return $this;
    }

    protected function compareDeletedAttributes(): self
    {
        $this->deletedAttributes = array_diff_key($this->doctrineAttributes, $this->modelAttributes);
        return $this;
    }

    private function wrapAttributes(): self
    {
        if (empty($this->addedAttributes) && empty($this->changedAttributesFromModel) && empty($this->changedAttributesFromDoctrine) && empty($this->deletedAttributes)) {
            return $this;
        }

        $this->isChanged = true;

        $this->allAttributes = [
            'added' => $this->addedAttributes,
            'changed' => [
                'fromModel' => $this->changedAttributesFromModel,
                'fromDoctrine' => $this->changedAttributesFromDoctrine,
            ],
            'deleted' => $this->deletedAttributes,
        ];

        return $this;
    }

    public function isChanged(): bool
    {
        return $this->isChanged;
    }

    public function getAllAttributes()
    {
        return $this->allAttributes;
    }
}
