<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers\Definition;

use Closure;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Eyadhamza\LaravelEloquentMigration\Core\Mappers\Mapper;
use Illuminate\Database\Schema\ColumnDefinition;

class MapToColumnDefinition
{

    public function handle(Mapper $mapper, Closure $next): Mapper
    {
        $columns = $mapper->getColumns()
            ->reject(fn(Column $column) => $mapper->getForeignKeys()->contains(fn(ForeignKeyConstraint $foreignKey) => $foreignKey->getLocalColumns()[0] === $column->getName()))
            ->map(function (Column $column) {
                if ($column->getName() == 'id'){
                    return new ColumnDefinition($this->mapToIdColumn($column));
                }
                return new ColumnDefinition($this->mapToColumn($column));
            });


        $mapper->setColumns($columns);

        return $next($mapper);
    }

    protected function mapToColumn(Column $column): array
    {
        return collect([
            'name' => $column->getName(),
            'type' => $this->mapToColumnType($column),
            'unsigned' => $column->getUnsigned(),
            'nullable' => $column->getNotnull() == false,
            'default' => $column->getDefault(),
            'length' => $column->getLength(),
            'scale' => $column->getScale(),
            'comment' => $column->getComment(),
            'autoIncrement' => $column->getAutoincrement(),
            'fixed' => $column->getFixed(),
        ])->filter()->toArray();
    }

    private function mapToColumnType(Column $column): string
    {
        return match ($column->getType()->getName()) {
            'datetime' => 'timestamp',
            'bigint' => 'bigInteger',
            'smallint' => 'smallInteger',
            'tinyint' => 'tinyInteger',
            default => $column->getType()->getName(),
        };
    }

    private function mapToIdColumn(Column $column)
    {
        return [
            'name' => $column->getName(),
            'type' => 'id',
        ];
    }

}
