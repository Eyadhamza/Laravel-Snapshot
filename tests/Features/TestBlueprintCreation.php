<?php


use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;
use Eyadhamza\LaravelAutoMigration\Core\MigrationMapper;
use Eyadhamza\LaravelAutoMigration\Core\ModelToBlueprintMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Collection;

it('can create a new MigrationMapper instance', function () {
    $mapper = MigrationMapper::make();
    expect($mapper)->toBeInstanceOf(MigrationMapper::class);
});

it('can map models to blueprints', function () {
    $mapper = MigrationMapper::make();
    $blueprints = $mapper->getModelBlueprints();

    $blueprint = $blueprints->first();

        expect($blueprint)
            ->toBeInstanceOf(Blueprint::class)
            ->toHaveProperty('table');

});
it('can generate the right columns', function () {
    $mapper = MigrationMapper::make();

    $blueprints = $mapper->getModelBlueprints();
    expect($blueprints->first()->getColumns())
        ->toHaveCount(4);
    $idColumn = $blueprints->first()->getColumns()[0];
        expect($idColumn)
            ->toBeInstanceOf(ColumnDefinition::class)
            ->and($idColumn->getAttributes())
            ->toHaveKey('type', 'bigInteger')
            ->toHaveKey('name', 'id')
            ->toHaveKey('autoIncrement', true)
            ->toHaveKey('unsigned', true)
            ->toHaveKey('unique', true)
            ->toHaveKey('primary', true)
            ->toHaveKey('after', 'email');
});
