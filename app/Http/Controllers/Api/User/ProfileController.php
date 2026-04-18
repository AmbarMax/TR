<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\Api\Profile\UpdateAvatarRequest;
use App\Http\Requests\Api\Profile\UpdateBackgroundRequest;
use App\Http\Requests\Api\Profile\UpdatePasswordRequest;
use App\Http\Requests\Api\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\UserResource;
use App\Models\Country;
use App\Services\Api\UserService;
use http\Env\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProfileController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function index()
    {
        return response()->json([
            'user' => ProfileResource::make(Auth::user()
                ->getUserDataWithBalances())
                ->response()->getData(true)
        ], ResponseAlias::HTTP_OK);
    }

    public function balances()
    {
        return response()->json([
            'userBalances' => Auth::user()->balances()->get()
        ], ResponseAlias::HTTP_OK);
    }

    public function update(UpdateProfileRequest $request)
    {
        return $this->userService->update(Auth::user()->id, $request->toArray())
            ?response()->json([
                'message' => 'Profile successfully updated'
            ], ResponseAlias::HTTP_CREATED)
            :response()->json([
                'message' => 'Profile not updated'
            ], ResponseAlias::HTTP_BAD_REQUEST);
    }
    public function updatePassword(updatePasswordRequest $request)
    {
        $this->userService->updatePassword(Auth::user()->id, $request->validated())
            ?response()->json([
            'message' => 'Password successfully updated'
        ], ResponseAlias::HTTP_CREATED)
            :response()->json([
            'message' => 'Password not updated'
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function updateAvatar(UpdateAvatarRequest $request){
        $this->userService->updateAvatar(Auth::user()->id, $request->all())
            ?response()->json([
                'message' => 'Avatar successfully updated'
        ], ResponseAlias::HTTP_CREATED)
            :response()->json([
                'message' => 'Avatar not updated'
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function updateBackground(UpdateBackgroundRequest $request){
        $this->userService->updateBackground(Auth::user()->id, $request->all())
            ?response()->json([
                'message' => 'Background successfully updated'
        ], ResponseAlias::HTTP_CREATED)
            :response()->json([
                'message' => 'Background not updated'
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function allCountries()
    {
        return response()->json([
            'countries' => Country::all(),
        ], ResponseAlias::HTTP_OK);
    }

    public function updateVirtualHall(HttpRequest $request): JsonResponse
    {
        $data = $request->only([
            'social_twitter', 'social_twitch', 'social_youtube',
            'social_instagram', 'social_discord_tag', 'social_website',
            'featured_slots',
        ]);

        return $this->userService->updateVirtualHall(Auth::user()->id, $data)
            ? response()->json(['message' => 'Virtual Hall updated'], ResponseAlias::HTTP_OK)
            : response()->json(['message' => 'Update failed'], ResponseAlias::HTTP_BAD_REQUEST);
    }
}
