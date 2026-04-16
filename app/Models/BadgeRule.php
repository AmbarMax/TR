<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BadgeRule extends Model
{
    use HasFactory, UUID;

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
}
