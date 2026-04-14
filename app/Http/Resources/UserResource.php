<?php

namespace App\Http\Resources;

use App\Enums\AvatarType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'username' => $this->username,
            'avatar' => $this->avatar ? $this->getAvatarFile(AvatarType::Medium()) : null,
            'background' => $this->background ? $this->getBackgroundFile() : null,
            'balances' => $this->balances,
            'badges' => BadgeResource::collection(User::find($this->id)->publicBadges()->get())
                ->response()->getData(true),
            'trophies' => $this->trophies,
            'country_id' => $this->country_id,
            'description' => $this->description,
        ];
    }
}
