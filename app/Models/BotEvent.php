<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BotEvent extends Model
{
    use HasFactory, UUID, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        // Schema uses `title` (not `name`).
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'starts_at', 'ends_at', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('event');
    }
}
