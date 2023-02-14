<?php

namespace App\Models;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Column;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\AsDefault;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Nullable;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Primary;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Required;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Unique;
use Eyadhamza\LaravelAutoMigration\Core\Mappers\Column;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    #[Column(Column::BIGINTEGER), Unique, Primary]
    protected int $id;

    #[Column, Nullable, AsDefault('Eyad Hamza')]
    protected string $name;

    #[Column, Unique]
    protected string $email;

    #[Column, Required]
    protected string $password;


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
