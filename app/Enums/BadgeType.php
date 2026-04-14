<?php
namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

class BadgeType extends Enum
{
    const Common = 0;
    const DiscordRole = 1;
    const DiscordBadge = 2;
    const DiscordBotBadge = 3;
}
