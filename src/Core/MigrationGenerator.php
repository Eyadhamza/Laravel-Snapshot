<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\AttributeEntity;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\ForeignKeys\ForeignKeyMapper;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\IndexMapper;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class MigrationGenerator
{
    private Collection $generated;
    private string $tableName;

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
        $this->generated = new Collection;
    }

    public static function make(string $tableName): MigrationGenerator
    {
        return new self($tableName);
    }

    public function generateMigrationFile(string $migrationFilePath, string $operation): void
    {
        $generatedMigrationFile = $this->replaceStubMigrationFile($operation);
        file_put_contents($migrationFilePath, $generatedMigrationFile);
    }
    private function replaceStubMigrationFile(string $operation): string
    {
        $fileContent = file_get_contents("stubs/$operation-migration.stub");
        $fileContent = Str::replace("\$tableName", $this->tableName, $fileContent);

        return Str::replace("{{ \$mappedColumns }}", $this->generated->join("\n \t \t \t"), $fileContent);
    }

    public function addColumn(AttributeEntity|Fluent $column,string $columnName = null): self
    {
        $columnType = $column->get('type');
        $columnName = $column->get('name') ?? $columnName;
        $rules = $column->get('rules');
        $mappedColumn = "\$table" . "->$columnType" . "({$this->getColumnNameOrNames($columnName)})";

        if (!$rules) {
            $this->generated->add($mappedColumn . ";");
            return $this;
        }
        foreach ($rules as $rule => $value) {
            if ($this->inForeignRules($value) || is_int($rule)) {
                $mappedColumn = $mappedColumn . "->$value()";
                continue;
            }
            $mappedColumn = $mappedColumn . "->$rule('$value')";
        }
        $this->generated->add($mappedColumn . ";");

        return $this;
    }

    public function modifyColumn(Fluent $column, Collection $attributes): self
    {
        $columnType = $column->get('type');
        $columnName = $column->get('name');

        $mappedColumn = "\$table" . "->$columnType" . "('$columnName')";
        collect($attributes)
            ->reject(fn($value, $attribute) => $this->attributesToBeSkipped($attribute))
            ->reject(fn($value, $attribute) => $this->noChangeHappened($attribute))
            ->map(function ($value, $attribute) use ($columnName, $columnType, $mappedColumn, $column) {
                if ($this->attributesAsSecondArgument($attribute)) {
                    return $value ? "\$table->$columnType('$columnName', $value)" . "->change();" : "";
                }
                if ($this->inForeignRules($attribute)) {
                    return "";
                }
                return $mappedColumn . "->$attribute()" . "->change();";
            });
        $this->generated->add($mappedColumn);
        return $this;
    }

    public function removeColumn($column): self
    {
        $columnName = $column->get('name');
        $this->generated->add("\$table->dropColumn('$columnName');");
        return $this;
    }

    public function buildIndex(IndexDefinition $matchedIndex, Collection $modifiedAttributes): self
    {
        $indexType = $matchedIndex->get('name');
        $indexColumns = $this->getColumns($matchedIndex);
        $mappedIndex = "\$table" . "->$indexType" . "($indexColumns)";
        collect($modifiedAttributes)->filter(function ($value, $attribute) use ($matchedIndex) {
            return $value !== $matchedIndex->get($attribute);
        })->map(function ($value, $attribute) use ($indexType, $mappedIndex) {
            return $mappedIndex . "->$attribute()" . "->change();";
        });
        $this->generated->add($mappedIndex);
        return $this;
    }

    public function addIndex(Fluent|AttributeEntity $index): self
    {
        $indexNames = $this->getColumns($index);
        $this->generated->add("\$table->index($indexNames);");
        return $this;
    }

    public function removeIndex(IndexDefinition $index): self
    {
        $indexNames = $this->getColumns($index);
        $this->generated->add("\$table->dropIndex($indexNames);");

        return $this;
    }

    public function buildForeignKey(ForeignKeyDefinition $foreignKey, Collection $modifiedAttributes): self
    {
        $foreignKeyColumn = $foreignKey->get('name');
        $mappedForeignKey = "\$table" . "->foreign" . "($foreignKeyColumn)";
         collect($modifiedAttributes)->filter(function ($value, $attribute) use ($foreignKey) {
            return $value !== $foreignKey->get($attribute);
        })->map(function ($value, $attribute) use ($mappedForeignKey) {
            return $mappedForeignKey . "->$attribute()" . "->change();";
        });
        $this->generated->add($mappedForeignKey);
        return $this;
    }

    public function addForeignKey(AttributeEntity|ForeignKeyDefinition $foreignKey): static
    {
        $columnName = $foreignKey->get('columns');
        if (empty($foreignKey->get('rules'))) {
            $this->generated->add("\$table->foreign('$columnName');");
            return $this;
        }
        foreach ($foreignKey->get('rules') as $rule => $value) {
            if ($this->inForeignRules($value)) {
                $columnName = $columnName . "->$value('$rule')";
                continue;
            }
            $columnName = $columnName . "->$rule('$value');";
        }
        $this->generated->add($columnName);
        return $this;
    }

    public function removeForeignKey(ForeignKeyDefinition $foreignKey): self
    {
        $foreignKeyName = $foreignKey->get('name');
        $this->generated->add("\$table->dropForeign('$foreignKeyName');");
        return $this;
    }

    public function getGenerated()
    {
        return $this->generated->flatten()->filter()->values();
    }

    private function attributesAsSecondArgument($attribute): bool
    {
        return in_array($attribute, ['length', 'precision', 'scale']);
    }

    private function inForeignRules($rule): bool
    {
        return in_array($rule, ['cascadeOnDelete', 'cascadeOnUpdate']);
    }

    private function attributesToBeSkipped(int|string|null $attribute): bool
    {
        return in_array($attribute, ['name', 'type']);
    }

    private function noChangeHappened($attribute): bool
    {
        return in_array($attribute, ['autoIncrement']);
    }


    private function getColumns(AttributeEntity|IndexDefinition $matchedIndex): string
    {
        if (is_string($matchedIndex->get('columns'))) {
            return "'{$matchedIndex->get('columns')}'";
        }
        if (! $matchedIndex->get('columns')) {
            return '';
        }
        return "['" . implode("','" , $matchedIndex->get('columns')) . "']";
    }

    private function getColumnNameOrNames(string|array|null $columnName): string
    {
        return is_array($columnName) ? "['" . implode("','", $columnName) . "']" : "'$columnName'";
    }
}
