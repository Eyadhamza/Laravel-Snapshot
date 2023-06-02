<?php

namespace App\Models;

use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\AsString;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Id;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignId;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\Index;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\Unique;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\Rule;
use Illuminate\Database\Eloquent\Model;


#[Id('id', [Rule::UNSIGNED, Rule::AUTO_INCREMENT])]
#[AsString('title', [Rule::DEFAULT => 'Eyad Hamza'])]
#[AsString('footer', [Rule::NULLABLE])]
#[AsString('description', [Rule::NULLABLE])]
#[ForeignId('author_id', [Rule::CONSTRAINED => 'savers', Rule::CASCADE_ON_DELETE, Rule::CASCADE_ON_UPDATE])]
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
