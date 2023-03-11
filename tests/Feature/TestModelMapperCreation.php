<?php


use Eyadhamza\LaravelAutoMigration\Core\MigrationBuilder;
use Eyadhamza\LaravelAutoMigration\Core\ModelMapper;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\File;
use Spatie\ModelInfo\ModelInfo;


it('can create a new MigrationBuilder instance', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));
    expect($mapper)
        ->toBeInstanceOf(MigrationBuilder::class);
});

it('can map models to mapper objects', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));

    $modelMapper = $mapper->getModelMappers()->first();
    expect($modelMapper)
        ->toBeInstanceOf(ModelMapper::class);

});
it('can generate the right columns', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));
    $modelMapper = $mapper->getModelMappers()->first();
    expect($modelMapper->getColumns())
        ->toHaveCount(3);
    $idColumn = $modelMapper->getColumns()->get('id');
        expect($idColumn)
            ->toBeInstanceOf(ColumnDefinition::class)
            ->and($idColumn->getAttributes())
            ->toHaveKey('type', 'id')
            ->toHaveKey('name', 'id')
            ->toHaveKey('autoIncrement', true);
});

it('builds migrations files', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));

    $file = collect(File::allFiles(database_path('migrations')))
        ->first();


    expect($file->getContents())
        ->toContain('Schema::create(\'users\', function (Blueprint $table) {')
        ->toContain("\$table->id('id')")
        ->toContain("\$table->string('email')")
        ->toContain("\$table->string('password')")
        ->toContain('Schema::dropIfExists(\'users\');');
});

afterEach(function () {
    File::deleteDirectory(database_path('migrations'), true);
});
