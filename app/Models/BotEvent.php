<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotEvent extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'guild_id',
        'badge_rule_id',
        'title',
        'description',
        'channel_id',
        'discord_event_id',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function guildConnection()
    {
        return $this->belongsTo(GuildConnection::class, 'guild_id', 'guild_id');
    }

    public function badgeRule()
    {
        return $this->belongsTo(BadgeRule::class);
    }
}
