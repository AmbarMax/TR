<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SteamUserAchievement extends Pivot
{
    protected $table = 'steam_user_achievements';

    protected $casts = [
        'unlocked_at' => 'datetime',
    ];
}
