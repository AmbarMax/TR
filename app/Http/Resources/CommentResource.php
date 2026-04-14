<?php

namespace App\Http\Resources;

use App\Enums\AvatarType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'creator' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'avatar' =>  $this->user->avatar ? $this->user->getAvatarFile(AvatarType::Small()) : null,
            ],
            'body' => $this->body,
            'created_at' => Carbon::parse($this->created_at)->format('F j, Y')
        ];
    }
}
