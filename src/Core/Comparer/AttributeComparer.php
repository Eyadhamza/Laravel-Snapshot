<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Comparer;

use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Schema\Column;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
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

    public function __construct(AbstractAsset $modelColumn, AbstractAsset $doctrineColumn)
    {
        $this->modelAttributes = $modelColumn->toArray();
        $this->doctrineAttributes = $doctrineColumn->toArray();
    }

    public static function make(AbstractAsset $modelColumn, AbstractAsset $doctrineColumn): AttributeComparer
    {
        return new self($modelColumn, $doctrineColumn);
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
            Arr::except($this->modelAttributes, ['columns', 'type']),
            Arr::except($this->doctrineAttributes, ['columns', 'type'])
        );
        return $this;
    }

    protected function compareChangedAttributesFromDoctrine(): self
    {
        $this->changedAttributesFromDoctrine = array_diff_assoc(
            Arr::except($this->doctrineAttributes, ['columns', 'type']),
            Arr::except($this->modelAttributes, ['columns', 'type'])
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
            MigrationOperationEnum::Add->value => $this->addedAttributes,
            MigrationOperationEnum::Modify->value => [
                'fromModel' => $this->changedAttributesFromModel,
                'fromDoctrine' => $this->changedAttributesFromDoctrine,
            ],
            MigrationOperationEnum::Remove->value => $this->deletedAttributes,
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
