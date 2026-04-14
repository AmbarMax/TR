<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $body
 * @property string $user_id
 * @property string $commentable_id
 * @property string $commentable_type
 */
class Comment extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'body',
        'commentable_id',
        'commentable_type',
        'user_id',
    ];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
