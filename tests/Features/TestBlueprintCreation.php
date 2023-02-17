<?php


use App\Models\User;
use Eyadhamza\LaravelAutoMigration\Core\BlueprintBuilder;
use Eyadhamza\LaravelAutoMigration\Core\MapToMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\File;


it('can create a new MapToMigration instance', function () {
    $mapper = MapToMigration::make();
    expect($mapper)->toBeInstanceOf(MapToMigration::class);
});

it('can map models to blueprints', function () {
    $mapper = MapToMigration::make();
    $blueprints = $mapper->getBlueprints();
    $blueprint = $blueprints->first();
        expect($blueprint)
            ->toBeInstanceOf(Blueprint::class)
            ->toHaveProperty('table');

});
it('can generate the right columns', function () {
    $mapper = MapToMigration::make();

    $blueprints = $mapper->getBlueprints();
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

    $file = collect(File::allFiles(database_path('migrations')))
        ->first();


    expect($file->getContents())
        ->toContain('Schema::create(\'books\', function (Blueprint $table) {')
        ->toContain("\$table->bigInteger('id')->unique()->primary()->autoIncrement()->unsigned()")
        ->toContain("\$table->string('title')->unique()")
        ->toContain("\$table->string('description')")
        ->toContain("\$table->foreignId('author_id')")
        ->toContain("\$table->timestamps()")
        ->toContain('$table->timestamps();')
        ->toContain('Schema::dropIfExists(\'books\');');

    File::deleteDirectory(database_path('migrations'), true);
});

