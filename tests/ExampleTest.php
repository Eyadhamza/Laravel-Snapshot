<?php


use Eyadhamza\LaravelAutoMigration\Core\MigrationMapper;
use Illuminate\Support\Str;

it('can test', function () {

    $reflect = new ReflectionClass(\Illuminate\Database\Schema\Blueprint::class);

    $methods = collect($reflect->getMethods())->map(fn(ReflectionMethod $method) => $method->getName());

    dd($methods->each(fn($method) => dump( "const ". Str::upper($method) . " = " . "$method")));
    dd(MigrationMapper::make());
});
