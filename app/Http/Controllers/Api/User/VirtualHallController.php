<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Resources\UserResource;
use App\Services\Api\FollowService;
use App\Services\Api\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class VirtualHallController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function show($username): JsonResponse
    {
        $user = $this->userService?->findByUserName($username)?->getUserDataWithBalances();
        $user = UserResource::make($user)->response()->getData(true);

        $filteredTrophies = array_filter($user['data']['trophies'], function ($trophy) {
            return $trophy['pivot']['display'] === 1;
        });
        $achievements = $this->userService->findByUserName($username)->achievements;
        $filteredAchievements = $achievements->filter(function ($achievement) {
            return $achievement->display == 1 && $achievement->status == 1;
        });
        $user['data']['trophies'] = array_values($filteredTrophies);
        $user['data']['achievements'] = array_values($filteredAchievements->toArray());
        $followStatus = Auth::user() ? FollowService::checkSubscriptionStatus(Auth::user(), $user['data']['id']) : null;

        $userModel = $this->userService->findByUserName($username);
        $socialLinks = [
            'twitter'     => $userModel->social_twitter,
            'twitch'      => $userModel->social_twitch,
            'youtube'     => $userModel->social_youtube,
            'instagram'   => $userModel->social_instagram,
            'discord_tag' => $userModel->social_discord_tag,
            'website'     => $userModel->social_website,
        ];
        $featuredSlots = $userModel->featured_slots ?? [];

        return $user ? response()->json([
            'user'          => $user,
            'followStatus'  => $followStatus,
            'socialLinks'   => $socialLinks,
            'featuredSlots' => $featuredSlots,
        ], ResponseAlias::HTTP_OK) : response()->json([
            'message' => 'User not found'
        ], ResponseAlias::HTTP_NOT_FOUND);
    }
}
