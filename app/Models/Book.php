<?php

namespace App\Models;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\BigInteger;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ForeignId;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\AsString;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Rule;
use Illuminate\Database\Eloquent\Model;


#[BigInteger('id', Rule::UNIQUE, Rule::PRIMARY, Rule::AUTO_ICREMENT, Rule::UNSIGNED)]
#[AsString('title', Rule::UNIQUE)]
#[AsString('description')]
#[ForeignId('author_id')]
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
