<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $discord_id
 * @property string $color
 * @property boolean $hoist
 * @property string $icon
 * @property string $unicode_emoji
 * @property int $position
 * @property int $permissions
 * @property boolean $managed
 * @property boolean $mentionable
 * @property array $tags
 * @property int $flags
 * @property string $badge_id
 */
class DiscordRole extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'discord_id',
        'color',
        'hoist',
        'icon',
        'unicode_emoji',
        'position',
        'permissions',
        'managed',
        'mentionable',
        'tags',
        'flags',
        'badge_id',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

}
