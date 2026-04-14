<?php
namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

class DiscordBadges extends Enum
{
    const STAFF = 0;
    const PARTNER = 1;
    const HYPESQUAD = 2;
    const BUG_HUNTER_LEVEL_1 = 3;
    const HYPESQUAD_ONLINE_HOUSE_1 = 6;
    const HYPESQUAD_ONLINE_HOUSE_2 = 7;
    const HYPESQUAD_ONLINE_HOUSE_3 = 8;
    const PREMIUM_EARLY_SUPPORTER = 9;
    const TEAM_PSEUDO_USER = 10;
    const BUG_HUNTER_LEVEL_2 = 14;
    const VERIFIED_BOT = 16;
    const VERIFIED_DEVELOPER = 17;
    const CERTIFIED_MODERATOR = 18;
    const BOT_HTTP_INTERACTIONS = 19;
    const ACTIVE_DEVELOPER = 22;

    public static function getStringForBadge($badge)
    {
        return config('integrations.discord.badge_titles')[$badge] ?? "Unknown";
    }

    public static function getImageForBadge($badge)
    {
        return config('integrations.discord.images')[$badge] ?? "Unknown";
    }
}

