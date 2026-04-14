<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $appid
 * @property string $name
 * @property string|null $img_icon_url
 * @property string|null $img_logo_url
 */
class SteamGame extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'appid',
        'name',
        'img_icon_url',
        'img_logo_url',
    ];

    protected $casts = [
        'appid' => 'integer',
    ];

    public function achievements()
    {
        return $this->hasMany(SteamAchievement::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'steam_user_games')
            ->withPivot('playtime_minutes')
            ->withTimestamps();
    }
}
