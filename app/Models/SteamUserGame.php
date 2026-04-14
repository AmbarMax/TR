<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SteamUserGame extends Pivot
{
    protected $table = 'steam_user_games';

    protected $casts = [
        'playtime_minutes' => 'integer',
    ];
}
