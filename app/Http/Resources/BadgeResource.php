<?php

namespace App\Http\Resources;

use App\Enums\BadgeType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'description' => $this->description,
            'integration' => $this->integration->name,
            'display' => !!$this->pivot->display,
            'is_share' => !!$this->pivot->is_share,
            'type' => $this->type,
            'info' => $this->type == BadgeType::DiscordRole ? $this->discordRole : null,
        ];
    }
}
