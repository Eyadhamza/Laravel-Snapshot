<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Formatters;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;
use Eyadhamza\LaravelEloquentMigration\Core\Mappers\ElementToCommandMapper;

abstract class Formatter
{
    protected ElementToCommandMapper $element;
    protected string $formattedCommand = '';
    protected MigrationOperationEnum $operation;
    protected array $options;


    public function format(): string
    {
        $this
            ->reset()
            ->formatOperation()
            ->addTablePrefix()
            ->formatType()
            ->formatName()
            ->formatOptions()
            ->formatEnd();

        return $this->formattedCommand;
    }

    abstract protected function formatOperation(): self;

    protected function formatOptions(): self
    {
        $this->append(
            collect($this->options)
                ->filter(fn($value, $rule) => $value !== null)
                ->except(['type', 'name'])
                ->map(fn($value, $rule) => $this->matchRule($rule, $value))
                ->join('')
        );

        return $this;
    }


    protected function formatEnd(): self
    {
        $this->append(';');
        return $this;
    }

    protected function addTablePrefix(): self
    {
        $this->append("\$table->");

        return $this;
    }

    protected function formatType(): self
    {
        $this->append($this->element->getType());

        return $this;
    }

    protected function formatName(): self
    {
        if (!$this->element->getName() || $this->specialColumnName()) {
            $this->append("()");
            return $this;
        }

        if (is_array($this->element->getName())) {
            $name = count($this->element->getName()) === 1 ? "'{$this->element->getName()[0]}'" : "['" . implode("','", $this->element->getName()) . "']";
            $this->append("($name)");
            return $this;
        }

        $this->append("('{$this->element->getName()}')");

        return $this;
    }

    protected function append(string $value): static
    {
        $this->formattedCommand .= $value;

        return $this;
    }

    private function reset(): self
    {
        $this->formattedCommand = '';

        return $this;
    }

    private function matchRule(string $rule, int|string|null $value): ?string
    {
        return match ($rule){
            'notnull' => $this->addRule('nullable', !$value),
            'autoincrement' => $this->addRule('autoIncrement', (bool)$value),
            'default' => $this->addRule('default', $value),
            'unsigned' => $this->addRule('unsigned', (bool)$value),
            'comment' => $this->addRule('comment', $value),
            default => null,
        };
    }
    private function addRule(string $rule, string|bool $value = null): string
    {
        if ($this->hasSpecialName($rule)) {
            return '';
        }
        if (is_bool($value)) {
            return  $value === false ? '' : "->$rule()";
        }

        $methodParameters = is_string($value) ? "'$value'" : '';

        return "->$rule($methodParameters)";
    }

    private function specialColumnName(): bool
    {
        return in_array($this->element->getName(), ['rememberToken', 'softDeletes', 'timestamps', 'id']);
    }


    private function hasSpecialName(): bool
    {
        return in_array($this->element->getName(), ['id']);
    }

    public function setElement(ElementToCommandMapper $element): static
    {
        $this->element = $element;
        return $this;
    }

    public function setOperation(MigrationOperationEnum $operation): static
    {
        $this->operation = $operation;
        return $this;
    }

    public function setOptions($options): static
    {
        $this->options = $options;
        return $this;
    }

}
