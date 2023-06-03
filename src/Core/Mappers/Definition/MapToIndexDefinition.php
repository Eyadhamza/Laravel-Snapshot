<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Mappers\Definition;

use Closure;
use Doctrine\DBAL\Schema\Index;
use Eyadhamza\LaravelEloquentMigration\Core\Mappers\Mapper;
use Illuminate\Database\Schema\IndexDefinition;

class MapToIndexDefinition
{

    public function handle(Mapper $mapper, Closure $next): Mapper
    {
        $indexes = $mapper->getIndexes()
            ->map(fn(Index $index) => new IndexDefinition($this->mapAttributesToIndex($index)));

        $mapper->setIndexes($indexes);

        return $next($mapper);
    }

    private function mapAttributesToIndex(Index $index): array
    {
        return collect([
            'primary' => $index->isPrimary(),
            'unique' => $index->isUnique(),
            'name' => $index->getName(),
            'algorithm' => $index->getFlags(),
            'columns' => $index->getColumns(),
        ])->filter()->toArray();
    }

}
