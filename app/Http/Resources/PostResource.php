<?php

namespace App\Http\Resources;

use App\Enums\AvatarType;
use App\Models\Badge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entity = $this->postable_type::where('id', $this->postable_id)->first();
        $badgeIntegration = null;
        if ($this->postable_type == Badge::class){
            $badgeIntegration = $entity->integration->name;
        }
        return [
            'id' => $this->id,
            'creator' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'avatar' =>  $this->user->avatar ? $this->user->getAvatarFile(AvatarType::Small()) : null,
            ],
            'donations' => $this->getDonationsSum(),
            'donations_count' => $this->uniquePayerCount(),
            'comments' => CommentResource::collection($this->getLastComments()),
            'comments_count' => $this->comments()->count(),
            'created_at' => Carbon::parse($this->created_at)->format('F j, Y'),
             'entity' => $entity,
            'badge_integration' => $badgeIntegration,
             'postable_type' => $this->postable_type,
        ];
    }
}
