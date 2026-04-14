<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $password
 */
class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, UUID;

    /**
     * @var string[] $fillable
     */
    protected $fillable = [
        'id', 'name', 'email', 'password', 'super_admin',
    ];

    /**
     * @var string[] $hidden
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
