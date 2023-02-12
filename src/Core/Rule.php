<?php

namespace Eyadhamza\LaravelAutoMigration\Core;
abstract class Rule
{
    const UNIQUE = 'unique';
    const NULLABLE = 'nullable';
    const PRIMARY = 'primary';
    const DEFAULT = 'default';
    public static function getRules(): array
    {
        return [
            self::UNIQUE => 'unique',
            self::NULLABLE => 'nullable',
            self::PRIMARY => 'primary',
            self::DEFAULT => 'default',
        ];
    }
}
