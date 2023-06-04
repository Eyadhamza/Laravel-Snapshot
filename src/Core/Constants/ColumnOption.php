<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Constants;

abstract class ColumnOption
{
    const AFTER = 'after';
    const DEFAULT = 'default';
    const NULLABLE = 'nullable';
    const UNIQUE = 'unique';
    const UNSIGNED = 'unsigned';
    const FIRST = 'first';
    const CHANGE = 'change';
    const COMMENT = 'comment';
    const AUTO_INCREMENT = 'autoIncrement';
    const LENGTH = 'length';
    const PRECISION = 'precision';
    const SCALE = 'scale';
    const FIXED = 'fixed';
    const DEFAULT_LENGTH = 0;


    public static function map($rules = null): array
    {
        if ($rules === null) {
            return $rules;
        }

        return collect($rules)->mapWithKeys(function ($value, $key) {
            if (is_int($key)) {
                $key = $value;
                $value = true;
            }
            return match ($key) {
                ColumnOption::LENGTH => ['length' => $value],
                ColumnOption::DEFAULT => ['default' => $value],
                ColumnOption::NULLABLE => ['notnull' => !$value],
                ColumnOption::PRECISION => ['precision' => $value],
                ColumnOption::SCALE => ['scale' => $value],
                ColumnOption::FIXED => ['fixed' => $value],
                ColumnOption::UNSIGNED => ['unsigned' => $value],
                ColumnOption::AUTO_INCREMENT => ['autoincrement' => $value],
                ColumnOption::COMMENT => ['comment' => $value],
                default => [],
            };
        })->toArray();
    }
}
