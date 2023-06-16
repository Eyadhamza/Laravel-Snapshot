<?php

namespace PiSpace\LaravelSnapshot\Core\Formatters;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;

class RemoveCommandFormatter extends Formatter
{
    protected function formatOptions(): self
    {
        return $this;
    }
    protected function formatType(): self
    {
        return match ($this->element->getDefinitionName()){
            Index::class => $this->append('dropIndex'),
            ForeignKeyConstraint::class => $this->append('dropForeign'),
            default => $this->append('dropColumn'),
        };
    }

}
