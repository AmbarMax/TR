<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $steam_game_id
 * @property string $api_name
 * @property string $display_name
 * @property string|null $description
 * @property string|null $icon_url
 * @property string|null $icon_gray_url
 * @property float|null $global_percent
 */
class SteamAchievement extends Model
{
    use HasFactory, UUID;

    protected $fillable = [
        'steam_game_id',
        'api_name',
        'display_name',
        'description',
        'icon_url',
        'icon_gray_url',
        'global_percent',
    ];

    protected $casts = [
        'global_percent' => 'decimal:2',
    ];

    public function game()
    {
        return $this->belongsTo(SteamGame::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'steam_user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }
}
