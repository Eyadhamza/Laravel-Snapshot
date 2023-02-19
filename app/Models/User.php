<?php

namespace App\Models;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\AsString;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Id;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Timestamps;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Rule;
use Illuminate\Database\Eloquent\Model;

#[AsString('name', [Rule::UNIQUE, Rule::DEFAULT => 'Eyad Hamza'])]
#[Id('id')]
#[AsString('description')]
#[AsString('email', [Rule::UNIQUE])]
#[AsString('password')]
#[Timestamps]

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
