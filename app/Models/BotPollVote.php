<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotPollVote extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'poll_id',
        'guild_id',
        'discord_user_id',
        'answer',
    ];

    public function poll()
    {
        return $this->belongsTo(BotPoll::class);
    }
}
