<?php

namespace App\Models;

use PiSpace\LaravelSnapshot\Core\Attributes\Columns\AsString;
use PiSpace\LaravelSnapshot\Core\Attributes\Columns\Id;
use PiSpace\LaravelSnapshot\Core\Attributes\Columns\Timestamps;
use PiSpace\LaravelSnapshot\Core\Attributes\ForeignKeys\ForeignId;
use PiSpace\LaravelSnapshot\Core\Attributes\Indexes\Index;
use PiSpace\LaravelSnapshot\Core\Attributes\Indexes\Unique;
use PiSpace\LaravelSnapshot\Core\Constants\ColumnOption;
use PiSpace\LaravelSnapshot\Core\Constants\ForeignKeyOption;
use Illuminate\Database\Eloquent\Model;


#[Id]
#[AsString('title', [ColumnOption::DEFAULT => '111 Hamza', ColumnOption::NULLABLE])]
#[AsString('footer', [ColumnOption::NULLABLE])]
#[AsString('test', [ColumnOption::NULLABLE])]
#[AsString('description', [ColumnOption::NULLABLE])]
#[ForeignId('user_id', [ForeignKeyOption::CONSTRAINED, ForeignKeyOption::CASCADE_ON_DELETE, ForeignKeyOption::CASCADE_ON_UPDATE])]
#[Index(['title', 'description'])]
#[Timestamps]
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
