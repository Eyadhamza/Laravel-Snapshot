<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers\Definition;

use Closure;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Eyadhamza\LaravelEloquentMigration\Core\Mappers\Mapper;
use Illuminate\Database\Schema\ForeignKeyDefinition;

class MapToForeignKeyDefinition
{

    public function handle(Mapper $mapper, Closure $next): Mapper
    {
        $foreignKeys = $mapper->getForeignKeys()->mapWithKeys(fn(ForeignKeyConstraint $foreignKey) => [
            $foreignKey->getName() => new ForeignKeyDefinition($this->mapToForeignKey($foreignKey))
        ]);

        $mapper->setForeignKeys($foreignKeys);

        return $next($mapper);
    }

    protected function mapToForeignKey(ForeignKeyConstraint $foreignKey): array
    {
        return collect([
            'name' => $foreignKey->getName(),
            'constrained' => $foreignKey->getForeignTableName(),
            'columns' => $foreignKey->getLocalColumns(),
            'cascadeOnDelete' => $foreignKey->getOption('onDelete') == 'CASCADE',
            'cascadeOnUpdate' => $foreignKey->getOption('onUpdate') == 'CASCADE',
        ])->filter()->toArray();
    }

}
