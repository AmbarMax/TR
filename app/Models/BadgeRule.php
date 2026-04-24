<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BadgeRule extends Model
{
    use HasFactory, UUID, LogsActivity;

    protected $fillable = [
        'badge_id',
        'guild_id',
        'platform',
        'trigger_type',
        'trigger_config',
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'active'         => 'boolean',
    ];

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    public function guildConnection()
    {
        return $this->belongsTo(GuildConnection::class, 'guild_id', 'guild_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        // Schema uses `trigger_type` + `trigger_config` + `active`
        // (not rule_type / condition / threshold / is_active).
        return LogOptions::defaults()
            ->logOnly(['name', 'platform', 'trigger_type', 'trigger_config', 'active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('badge_rule');
    }
}
