<?php


use App\Models\User;
use Eyadhamza\LaravelAutoMigration\Core\MigrationBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Spatie\ModelInfo\ModelInfo;

it('can create a new MigrationBuilder instance', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));
    expect($mapper->first())->toBeInstanceOf(MigrationBuilder::class);
});

it('can map models to blueprints', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));

    $blueprint = $mapper->first()->getBlueprint();

        expect($blueprint)
            ->toBeInstanceOf(Blueprint::class)
            ->toHaveProperty('table');

});
it('can generate the right columns', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));
    $blueprint = $mapper->first()->getBlueprint();
    expect($blueprint->getColumns())
        ->toHaveCount(4);
    $idColumn = $blueprint->getColumns()[0];
        expect($idColumn)
            ->toBeInstanceOf(ColumnDefinition::class)
            ->and($idColumn->getAttributes())
            ->toHaveKey('type', 'bigInteger')
            ->toHaveKey('name', 'id')
            ->toHaveKey('autoIncrement', true);
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
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));

    $file = collect(File::allFiles(database_path('migrations')))
        ->first();


    expect($file->getContents())
        ->toContain('Schema::create(\'books\', function (Blueprint $table) {')
        ->toContain("\$table->id('id')")
        ->toContain("\$table->string('title')")
        ->toContain("\$table->string('description')")
        ->toContain("\$table->foreignId('author_id')")
        ->toContain('Schema::dropIfExists(\'books\');');

});
//
//afterEach(function () {
//    File::deleteDirectory(database_path('migrations'), true);
//});
