<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLink extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'discord_user_id',
        'tr_user_id',
        'guild_id',
        'discord_username',
        'linked_at',
    ];

    protected $casts = [
        'linked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'tr_user_id');
    }

    public function guildConnection()
    {
        return $this->belongsTo(GuildConnection::class, 'guild_id', 'guild_id');
    }
}
