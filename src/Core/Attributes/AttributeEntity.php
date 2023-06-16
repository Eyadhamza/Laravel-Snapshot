<?php

namespace PiSpace\LaravelSnapshot\Core\Attributes;

use Attribute;



#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
abstract class AttributeEntity
{
    protected ?string $name;
    protected string $type;
    protected ?array $options;
    public function __construct(string $name = null, array $rules = [])
    {
        $this->name = $name;
        $this
            ->setOptions($rules)
            ->setType();
    }

    public function getName(): ?string
    {
        return $this->name ?? null;
    }
    abstract public function setType(): self;

    public function getType(): string
    {
        return $this->type;
    }
    abstract public function setDefinition(string $tableName): self;
    public function getOptions(): ?array
    {
        return $this->options;
    }

    abstract public function setOptions(array $options): self;

}
