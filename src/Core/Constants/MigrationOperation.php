<?php

namespace Eyadhamza\LaravelAutoMigration\Core\Constants;

enum MigrationOperation : string
{
    case Add = 'add';
    case Remove = 'remove';
    case Modify = 'modify';
}
