<?php

namespace Eyadhamza\LaravelAutoMigration\Core;

class MigrationCodeGenerator
{
    public static function make()
    {
        return new self();
    }

    public function handle()
    {
        //    private function generateMigrationCode(AttributeEntity|Column $column)
//    {
//
//        $mappedColumn = "\$table" . "->$columnType" . "({$this->getColumnNameOrNames($columnName)})";
//
//        if (!$rules) {
//            return $mappedColumn . ";";
//        }
//        foreach ($rules as $rule => $value) {
//            if ($this->inForeignRules($value)) {
//                $mappedColumn = $mappedColumn . "->{$value}('$rule')";
//                continue;
//            }
//            if (is_int($rule)) {
//                $mappedColumn = $mappedColumn . "->$value()";
//                continue;
//            }
//            $mappedColumn = $mappedColumn . "->{$rule}('$value')";
//        }
//        return $mappedColumn . ";";
//
//    }
    }
}
