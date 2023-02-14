<?php

namespace App\Models;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\BigInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ForeignId;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\AsString;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\After;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\AutoIncrement;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Index;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Primary;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Unique;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Unsigned;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    #[BigInteger, Unique, Primary, Unsigned, AutoIncrement, Index]
    protected int $id;

    #[AsString]
    protected string $title;

    #[AsString, Unique]
    protected string $email;

    #[AsString(255)]
    protected string $password;

    #[ForeignId, After('id')]
    protected int $author_id;
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
