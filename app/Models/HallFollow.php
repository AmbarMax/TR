<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallFollow extends Model
{
    use HasFactory;

    protected $table = 'hall_followers';

    protected $fillable = ['follower_id', 'hall_user_id'];

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function hallUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hall_user_id');
    }
}
