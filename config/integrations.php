<?php
use App\Enums\IntegrationType;
use App\Enums\DiscordBadges;

return [
    'name' => [
        IntegrationType::Github,
        IntegrationType::Discord,
        IntegrationType::Steam,
        IntegrationType::Riot,
        IntegrationType::Strava,
    ],
    'image' => [
        IntegrationType::Github  => 'https://github.githubassets.com/images/modules/profile/achievements/',
        IntegrationType::Discord => 'discord.png',
        IntegrationType::Steam   => 'https://community.akamai.steamstatic.com/public/images/badges/generic/',
        IntegrationType::Riot    => '',  // full URLs provided per-badge; no base prefix needed
        IntegrationType::Strava  => '',  // full URLs provided per-badge; no base prefix needed
    ],
    'discord' => [
        'badge_titles' => [
            DiscordBadges::STAFF => "Staff",
            DiscordBadges::PARTNER => "Partner",
            DiscordBadges::HYPESQUAD => "HypeSquad",
            DiscordBadges::BUG_HUNTER_LEVEL_1 => "Bug Hunter Level 1",
            DiscordBadges::HYPESQUAD_ONLINE_HOUSE_1 => "HypeSquad Online House 1",
            DiscordBadges::HYPESQUAD_ONLINE_HOUSE_2 => "HypeSquad Online House 2",
            DiscordBadges::HYPESQUAD_ONLINE_HOUSE_3 => "HypeSquad Online House 3",
            DiscordBadges::PREMIUM_EARLY_SUPPORTER => "Premium Early Supporter",
            DiscordBadges::TEAM_PSEUDO_USER => "Team Pseudo User",
            DiscordBadges::BUG_HUNTER_LEVEL_2 => "Bug Hunter Level 2",
            DiscordBadges::VERIFIED_BOT => "Verified Bot",
            DiscordBadges::VERIFIED_DEVELOPER => "Verified Developer",
            DiscordBadges::CERTIFIED_MODERATOR => "Certified Moderator",
            DiscordBadges::BOT_HTTP_INTERACTIONS => "Bot HTTP Interactions",
            DiscordBadges::ACTIVE_DEVELOPER => "Active Developer",
        ],
        'images' => [
            DiscordBadges::STAFF => "discordstaff.svg",
            DiscordBadges::PARTNER => "discordpartner.svg",
            DiscordBadges::HYPESQUAD => "discordnitro.svg",
            DiscordBadges::BUG_HUNTER_LEVEL_1 => "bughunterlevel1.svg",
            DiscordBadges::HYPESQUAD_ONLINE_HOUSE_1 => "hypesquadbravery.svg",
            DiscordBadges::HYPESQUAD_ONLINE_HOUSE_2 => "hypesquadbrilliance.svg",
            DiscordBadges::HYPESQUAD_ONLINE_HOUSE_3 => "hypesquadbalance.svg",
            DiscordBadges::PREMIUM_EARLY_SUPPORTER => "earlysupporter.svg",
            DiscordBadges::TEAM_PSEUDO_USER => "supportscommands.svg",
            DiscordBadges::BUG_HUNTER_LEVEL_2 => "bughunterlevel2.svg",
            DiscordBadges::VERIFIED_BOT => "discordnitro.svg",
            DiscordBadges::VERIFIED_DEVELOPER => "earlyverifiedbotdev.svg",
            DiscordBadges::CERTIFIED_MODERATOR => "certifiedmod.svg",
            DiscordBadges::BOT_HTTP_INTERACTIONS => "discordnitro.svg",
            DiscordBadges::ACTIVE_DEVELOPER => "activedeveloper.svg",
        ]
    ]
];
