<?php

namespace App\Http\Traits\Models\User;

use App\Enums\AvatarType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Vite;

trait GettersTrait
{
    public function formatDateOfBirth(): Attribute
    {
        return new Attribute(
            get: fn () => Carbon::parse($this->date_of_birth)->format(config('app.date_format')),
        );
    }

    public function formatEmailVerifiedAt(): Attribute
    {
        return new Attribute(
            get: fn () => Carbon::parse($this->email_verified_at)->format(config('app.date_format')),
        );
    }

    public function getAvatarFile(AvatarType $type): string
    {
        $suffix = match ($type) {
            AvatarType::Original() => config('app.default_avatar_suffix'),
            AvatarType::Medium() => config('app.medium_avatar_suffix'),
            AvatarType::Small() => config('app.small_avatar_suffix'),
            default => config('app.default_avatar_suffix'),
        };
        return $this->avatar
            ? url("storage/users/$this->id/avatar/$this->avatar".$suffix)
            : '/images/avatar/default-profile-img.png';
    }

    public function getBackgroundFile(): string
    {
        return $this->background
            ? url("storage/users/$this->id/background/$this->background")
            : '/images/background/default-background-img.png';
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
