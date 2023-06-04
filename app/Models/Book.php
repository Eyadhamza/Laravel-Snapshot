<?php

namespace App\Models;

use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\AsString;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Id;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignId;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\Index;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\Unique;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\ColumnOption;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\ForeignKeyOption;
use Illuminate\Database\Eloquent\Model;


#[Id]
#[AsString('title', [ColumnOption::DEFAULT => 'Eyad Hamza', ColumnOption::NULLABLE])]
#[AsString('footer', [ColumnOption::NULLABLE])]
#[AsString('test', [ColumnOption::NULLABLE])]
#[AsString('description', [ColumnOption::NULLABLE])]
#[ForeignId('user_id', [ForeignKeyOption::CASCADE_ON_DELETE, ForeignKeyOption::CASCADE_ON_UPDATE])]
#[Unique('title')]
#[Index(['title', 'description'])]
class Book extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
