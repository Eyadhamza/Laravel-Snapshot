<?php

namespace PiSpace\LaravelSnapshot\Core\Constants;

abstract class ForeignKeyOption
{
    const CASCADE_ON_DELETE = 'cascadeOnDelete';
    const CASCADE_ON_UPDATE = 'cascadeOnUpdate';
    const CONSTRAINED = 'constrained';
}
