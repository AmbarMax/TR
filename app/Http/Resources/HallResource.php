<?php

namespace App\Http\Resources;

use App\Enums\AvatarType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isBrand = $this->account_type === 'brand';

        return [
            'username'     => $this->username,
            'account_type' => $this->account_type,
            'name'         => $this->name,
            'avatar'       => $this->getAvatarFile(AvatarType::Medium()),
            'banner'       => $this->getBackgroundFile(),

            'verified_at'  => optional($this->verified_at)->toIso8601String(),
            'is_featured'  => (bool) $this->is_featured,
            'accent_color' => $this->accent_color,

            // `tagline` is the Hall-facing name for the existing `description`
            // column. No separate column is needed.
            'tagline'      => $this->description,

            'social' => [
                'twitter'   => $this->social_twitter,
                'twitch'    => $this->social_twitch,
                'youtube'   => $this->social_youtube,
                'instagram' => $this->social_instagram,
                'discord'   => $this->social_discord_tag,
                'website'   => $this->social_website,
            ],

            'stats' => $isBrand
                ? $this->resource->getBrandStats()
                : $this->resource->getPlayerStats(),

            // TODO Step 10/20: render a richer payload (trophy name, image,
            // rarity). For now expose the raw featured_slots JSON, which is
            // what the legacy VirtualHall endpoint already returned.
            'featured_items' => $this->featured_slots ?? [],
        ];
    }
}
