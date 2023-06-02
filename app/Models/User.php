<?php

namespace App\Models;

use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\AsString;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Id;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Timestamp;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Columns\Timestamps;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\ForeignKeys\ForeignId;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\Index;
use Eyadhamza\LaravelEloquentMigration\Core\Attributes\Indexes\Unique;
use Eyadhamza\LaravelEloquentMigration\Core\Constants\Rule;
use Illuminate\Database\Eloquent\Model;

#[AsString('name', [ Rule::DEFAULT => 'Eyad Hamza'])]
#[Id('id')]
#[AsString('email')]
#[AsString('password')]
#[Timestamp('created_at')]
#[Timestamp('updated_at')]
#[Index(['email'])]
class User extends Model
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
