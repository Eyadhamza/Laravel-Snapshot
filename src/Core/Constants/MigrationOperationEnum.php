<?php

namespace Eyadhamza\LaravelEloquentMigration\Core\Constants;

enum MigrationOperationEnum : string
{
    case Add = 'add';
    case Remove = 'remove';
    case Modify = 'modify';
}
