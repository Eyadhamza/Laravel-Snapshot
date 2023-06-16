<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use PiSpace\LaravelSnapshot\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)
    ->in('Feature');
