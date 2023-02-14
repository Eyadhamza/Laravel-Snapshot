<?php

namespace App\Models;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Property;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Primary;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Required;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Rules\Unique;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Type;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    #[Property(Type::BIGINTEGER), Unique, Primary]
    protected int $id;

    #[Property, Required]
    protected string $title;

    #[Property, Unique]
    protected string $email;

    #[Property, Required]
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
