<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotPoll extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'guild_id',
        'badge_rule_id',
        'title',
        'options',
        'channel_id',
        'discord_message_id',
        'duration_hours',
        'require_specific_answer',
        'status',
    ];

    protected $casts = [
        'options'                 => 'array',
        'require_specific_answer' => 'boolean',
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
