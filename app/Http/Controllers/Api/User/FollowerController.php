<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Follow\FollowActionRequest;
use App\Models\User;
use App\Repositories\Api\FollowerRepository;
use App\Services\Api\FollowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FollowerController extends Controller
{
    protected FollowerRepository $followerRepository;

    public function __construct(FollowerRepository $followerRepository)
    {
        $this->followerRepository = $followerRepository;
    }

    /** Followers list **/
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $page = $request->input('page', 1);
        $count = $request->input('count', 10);

        return response()->json([
            'followers' => $this->followerRepository->getFollowersPaginated($user, $page, $count),
            'followings' => $this->followerRepository->getFollowingPaginated($user, $page, $count)
        ]);
    }

    /** Following list **/
    public function following(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $page = $request->input('page', 1);
        $count = $request->input('count', 10);

        $following = $this->followerRepository->getFollowingPaginated($user, $page, $count);

        return response()->json([
            $following,
            'totalFollowers' => $user->followersCount(),
        ]);
    }

    /** Followers list **/
    public function followers(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $page = $request->input('page', 1);
        $count = $request->input('count', 10);

        $followers = $this->followerRepository->getFollowersPaginated($user, $page, $count);

        return response()->json([
            $followers,
            'totalFollowing' => $user->followingCount()
        ]);
    }

    /** Action to subscribe and unsubscribe **/
    public function action(FollowActionRequest $request): JsonResponse
    {
        $result = FollowService::action($request);

        return response()->json([
            'message' => Arr::get($result, 'message'),
            'followStatus' => Arr::get($result, 'followStatus'),
        ], Arr::get($result, 'status'));
    }

    /** Delete follower **/
    public function destroy(FollowActionRequest $request): JsonResponse
    {
        $result = FollowService::delete($request);

        return response()->json(['message' => Arr::get($result, 'message'),], Arr::get($result, 'status'));
    }
}