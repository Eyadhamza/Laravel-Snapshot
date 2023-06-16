<?php

namespace PiSpace\LaravelSnapshot\Core\Constants;

enum MigrationOperationEnum : string
{
    case Add = 'add';
    case Remove = 'remove';
    case Modify = 'modify';
}
