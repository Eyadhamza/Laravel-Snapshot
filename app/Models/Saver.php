<?php

namespace App\Models;

use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\AsString;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\ForeignId;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Id;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Columns\Timestamps;
use Eyadhamza\LaravelAutoMigration\Core\Attributes\Indexes\Unique;
use Eyadhamza\LaravelAutoMigration\Core\Constants\Rule;
use Illuminate\Database\Eloquent\Model;

#[Id('id')]
#[AsString('name', [Rule::DEFAULT => 'Eyad Hamza'])]
#[AsString('description')]
#[AsString('email')]
#[AsString('password')]
#[Timestamps]
#[Unique('email')]
#[ForeignId('user_id', [Rule::CONSTRAINED => 'users', Rule::CASCADE_ON_DELETE, Rule::CASCADE_ON_UPDATE])]
class Saver extends Model
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
