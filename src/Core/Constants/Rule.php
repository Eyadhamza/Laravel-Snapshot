<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Constants;

abstract class Rule
{
    const AFTER = 'after';
    const DEFAULT = 'default';
    const NULLABLE = 'nullable';
    const UNIQUE = 'unique';
    const UNSIGNED = 'unsigned';
    const PRIMARY = 'primary';
    const FIRST = 'first';
    const CHANGE = 'change';
    const COMMENT = 'comment';
    const INDEX = 'index';
    const FULL_TEXT = 'fullText';
    const AUTO_INCREMENT = 'autoIncrement';
    const CONSTRAINED = 'constrained';

    private static array $rules = [
        Rule::AFTER ,
        Rule::DEFAULT ,
        Rule::NULLABLE ,
        Rule::UNIQUE ,
        Rule::UNSIGNED,
        Rule::PRIMARY,
        Rule::FIRST,
        Rule::CHANGE,
        Rule::COMMENT,
        Rule::INDEX,
        Rule::FULL_TEXT,
        Rule::AUTO_INCREMENT,
    ];

}
