<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuildConnection extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'guild_id',
        'guild_name',
        'org_id',
        'bot_api_key',
        'bot_connected_at',
        'channel_cache_updated_at',
        'active',
    ];

    protected $casts = [
        'bot_connected_at'         => 'datetime',
        'channel_cache_updated_at' => 'datetime',
        'active'                   => 'boolean',
    ];

    public function channels()
    {
        return $this->hasMany(GuildChannel::class, 'guild_id', 'guild_id');
    }

    public function badgeRules()
    {
        return $this->hasMany(BadgeRule::class, 'guild_id', 'guild_id');
    }

    public function userLinks()
    {
        return $this->hasMany(UserLink::class, 'guild_id', 'guild_id');
    }

    public function polls()
    {
        return $this->hasMany(BotPoll::class, 'guild_id', 'guild_id');
    }

    public function events()
    {
        return $this->hasMany(BotEvent::class, 'guild_id', 'guild_id');
    }
}
