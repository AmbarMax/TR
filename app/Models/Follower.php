<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $user_id
 * @property string $follower_id
 * @property string  $created_at
 * @property string  $updated_at
 */
class Follower extends Model
{
    /** @inheritdoc */
    protected $table = 'followers';

    protected $fillable = [
        'user_id', 'follower_id'
    ];
}