<?php


use App\Models\User;
use Eyadhamza\LaravelAutoMigration\Core\MigrationBuilder;
use Eyadhamza\LaravelAutoMigration\Core\ModelMapper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Spatie\ModelInfo\ModelInfo;


it('can create a new MigrationBuilder instance', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));
    expect($mapper)
        ->toBeInstanceOf(MigrationBuilder::class);
});

it('can map models to blueprints', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));

    $blueprintBuilder = $mapper->getModelMappers()->first();
    expect($blueprintBuilder)
        ->toBeInstanceOf(ModelMapper::class)
        ->and($blueprintBuilder->getBlueprint())
        ->toBeInstanceOf(Blueprint::class)
        ->toHaveProperty('table');

});
it('can generate the right columns', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));
    $blueprint = $mapper->getModelMappers()->first()->getBlueprint();
    expect($blueprint->getColumns())
        ->toHaveCount(8);
    $idColumn = $blueprint->getColumns()[0];
        expect($idColumn)
            ->toBeInstanceOf(ColumnDefinition::class)
            ->and($idColumn->getAttributes())
            ->toHaveKey('type', 'bigInteger')
            ->toHaveKey('name', 'id')
            ->toHaveKey('autoIncrement', true);
});

it('builds migrations files', function () {
    $mapper = MigrationBuilder::mapAll(ModelInfo::forAllModels('app', config('auto-migration.base_path') ?? app_path()));

    $file = collect(File::allFiles(database_path('migrations')))
        ->first();


    expect($file->getContents())
        ->toContain('Schema::create(\'savers\', function (Blueprint $table) {')
        ->toContain("\$table->id('id')")
        ->toContain("\$table->string('name')")
        ->toContain("\$table->string('description')")
        ->toContain("\$table->foreignId('user_id')")
        ->toContain('Schema::dropIfExists(\'savers\');');
});

//afterEach(function () {
//    File::deleteDirectory(database_path('migrations'), true);
//});
