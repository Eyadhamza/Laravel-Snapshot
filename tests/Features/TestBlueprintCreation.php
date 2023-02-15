<?php


use App\Models\User;
use Eyadhamza\LaravelAutoMigration\Core\MapToMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;



it('can create a new MapToMigration instance', function () {
    $mapper = MapToMigration::make();
    expect($mapper)->toBeInstanceOf(MapToMigration::class);
});

it('can map models to blueprints', function () {
    $mapper = MapToMigration::make();
    $blueprints = $mapper->getModelBlueprints();

    $blueprint = $blueprints->first();

        expect($blueprint)
            ->toBeInstanceOf(Blueprint::class)
            ->toHaveProperty('table');

});
it('can generate the right columns', function () {
    $mapper = MapToMigration::make();

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
            ->toHaveKey('primary', true);
});
it('can do normal model operation', function () {
    User::create([
        'name' => 'Eyad',
        'email' => 'Eyadhamza0@gmail.com',
        'password' => 'password'
    ]);

    expect(User::all())
        ->toHaveCount(1);

    $user = User::first();
    expect($user->name)
        ->toBe('Eyad');
});

it('builds migrations files', function () {
    $mapper = MapToMigration::make();
    $mapper->buildMigrations();
});
