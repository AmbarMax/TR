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
            'account_type' => $this->account_type,
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            // Temporary backward-compat shim — remove in Brief 9N-C cleanup (post frontend refactor).
            // This keeps the staff moderation UI working in Vue components that still check
            // the legacy `Master user` role until the composable refactor (Steps 16-19) ships.
            'is_staff_legacy' => $this->hasAnyRole(['tr_moderator', 'tr_admin', 'tr_superadmin']),
            'social_twitter'     => $this->social_twitter,
            'social_twitch'      => $this->social_twitch,
            'social_youtube'     => $this->social_youtube,
            'social_instagram'   => $this->social_instagram,
            'social_discord_tag' => $this->social_discord_tag,
            'social_website'     => $this->social_website,
            'featured_slots'     => $this->featured_slots,
        ];
    }
}
