<?php

namespace App\Models;

use PiSpace\LaravelSnapshot\Core\Attributes\Columns\AsString;
use PiSpace\LaravelSnapshot\Core\Attributes\Columns\Id;
use PiSpace\LaravelSnapshot\Core\Attributes\Columns\Timestamps;
use PiSpace\LaravelSnapshot\Core\Attributes\Indexes\Index;
use PiSpace\LaravelSnapshot\Core\Constants\ColumnOption;
use Illuminate\Database\Eloquent\Model;


#[Id]
#[AsString('name', [ ColumnOption::DEFAULT => 'Eyad Hamza'])]
#[AsString('email')]
#[AsString('test', [ColumnOption::NULLABLE])]
#[AsString('password')]
#[Timestamps]
#[Index(['email','test'])]
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
