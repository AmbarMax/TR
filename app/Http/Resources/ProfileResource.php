<?php

namespace App\Http\Resources;

use App\Enums\AvatarType;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'email' => $this->email,
            'username' => $this->username,
            'date_of_birth' => $this->date_of_birth,
            'phone_number' => $this->phone_number,
            'avatar' => $this->getAvatarFile(AvatarType::Medium()),
            'background' => $this->getBackgroundFile(),
            'balances' => $this->balances,
            'country_id' => $this->country_id,
            'description' => $this->description,
            'unread_notifications_count' => Notification::getUnreadCount($this->id),
            'google2fa_status' => $this->google2fa_status,
            'roles' => $this->roles
        ];
    }
}
