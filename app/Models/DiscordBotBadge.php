<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $discord_id
 * @property string $prefix
 * @property string $badge_id
 */
class DiscordBotBadge extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'discord_id',
        'prefix',
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
