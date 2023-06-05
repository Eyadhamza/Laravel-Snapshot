<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Formatters;

use Eyadhamza\LaravelEloquentMigration\Core\Constants\MigrationOperationEnum;

abstract class Formatter
{
    protected string $formattedCommand = '';
    protected string|array|null $name;
    protected MigrationOperationEnum $operation;
    protected string $type;
    protected array $options;

    public function setNameOrNames(array|string|null $elementName): static
    {
        $this->name = $elementName;
        return $this;
    }

    public function setOperation(MigrationOperationEnum $operation): static
    {
        $this->operation = $operation;
        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function setOptions($options): static
    {
        $this->options = $options;
        return $this;
    }

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
        $this->append($this->type);

        return $this;
    }

    protected function formatName(): self
    {
        if (!$this->name || $this->specialColumnName()) {
            $this->append("()");
            return $this;
        }

        if (is_array($this->name)) {
            $name = count($this->name) === 1 ? "'{$this->name[0]}'" : "['" . implode("','", $this->name) . "']";
            $this->append("($name)");
            return $this;
        }

        $this->append("('$this->name')");

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
        return in_array($this->name, ['rememberToken', 'softDeletes', 'timestamps', 'id']);
    }


    private function hasSpecialName(): bool
    {
        return in_array($this->name, ['id']);
    }

}
