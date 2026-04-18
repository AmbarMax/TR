<?php

namespace App\Services\Api;

use App\Models\Badge;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Api\TrophyRepository;
use App\Repositories\Api\UserRepository;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedService
{
    private BaseRepository $postRepository;

    public function __construct(private readonly TrophyRepository $trophyRepository, private readonly UserRepository $userRepository)
    {
        $this->postRepository = new BaseRepository(Post::class);
    }


    public function share($id, $user_id, $type)
    {
        try {
            DB::beginTransaction();
            if ($type == 'trophy'){
                $created = $this->shareTrophy($id, $user_id);
            }
            if ($type == 'badge')
            {
                $created = $this->shareBadge($id, $user_id);
            }

            DB::commit();
            return $created;
        }catch (\Exception $e){
            Log::error('FeedService@share: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
    }

    private function shareBadge($id, $user_id)
    {
        $created = false;
        $badge = $this->userRepository->find($user_id)->badges()->find($id);
        if ($badge){
            $posts = $badge->posts();
            if (!$posts->where('user_id', $user_id)->exists()){
                $created = !!$posts->create([
                    'user_id' => $user_id
                ]);
            }
        }
        return $created;
    }

    private function shareTrophy($id, $user_id)
    {
        $created = false;

        $trophy = $this->trophyRepository->find($id);
        if ($trophy){
            $posts = $trophy->posts();
            if (!$posts->where('user_id', $user_id)->exists()){
                $created = !!$posts->create([
                    'user_id' => $user_id
                ]);
            }
        }
        return $created;
    }

    /**
     * Create a custom achievement and publish it to the feed in one step.
     * No validation protocol required.
     */
    public function createAndShareAchievement($request): array
    {
        try {
            DB::beginTransaction();

            $user = $request->user();

            // Save image
            $fileService = app(\App\Services\FileService::class);
            $image = $fileService->saveAchievementImage($request->file('image'));

            // Create achievement — auto-validated, auto-shared
            $achievement = new \App\Models\Achievement();
            $achievement->user_id = $user->id;
            $achievement->status = \App\Models\Achievement::VALIDATED;
            $achievement->image = $image;
            $achievement->name = $request->input('name');
            $achievement->description = $request->input('description');
            $achievement->is_share = true;
            $achievement->save();

            // Create post (publish to feed)
            $achievement->posts()->create([
                'user_id' => $user->id,
            ]);

            DB::commit();

            return [
                'message' => 'Achievement created and published!',
                'status' => 200,
                'achievement_id' => $achievement->id,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('FeedService@createAndShareAchievement: ' . $e->getMessage());
            return [
                'message' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }

    public function getUserPosts(User $user, int $page, ?int $count = null)
    {
        try {
            return $this->userRepository->find($user->id)->posts()->orderBy('created_at', 'desc')->paginate($count, ['*'], 'page', $page);
        }catch (\Exception $e){
            Log::error('FeedService@getUserPosts: ' . $e->getMessage());
            return null;
        }
    }

    public function getPosts($request)
    {
        $page = $request->input('page', 1);
        $count = $request->input('count', 10);

        try {
            $followingUserIds = $request->user()->following()->get()->pluck('id');
            return Post::whereIn('user_id', $followingUserIds)
                ->orderBy('created_at', 'desc')
                ->paginate($count, ['*'], 'page', $page);

        }catch (\Exception $e){
            Log::error('FeedService@getFeed: ' . $e->getMessage());
            return null;
        }
    }

    public function getFeed($skip=0, $take=10)
    {
        try {
            $followingUserIds = Auth::user()->following()->get()->pluck('id');
            return Post::whereIn('user_id', $followingUserIds)
                ->orderBy('created_at', 'desc')
//                ->offset($skip)
                ->limit($take)
                ->get();
        }catch (\Exception $e){
            Log::error('FeedService@getFeed: ' . $e->getMessage());
            return null;
        }
    }

    public function remove($id, $user_id){
        try {
            DB::beginTransaction();
            $post = $this->userRepository->find($user_id)->posts()->where('id', $id)->first();
            if($post->postable_type == Badge::class){
                $post->postable->users()->updateExistingPivot($user_id, ['is_share' => false]);
            }
            $isDeleted = $post->delete();
            DB::commit();
            return $isDeleted;
        }catch (\Exception $e){
            Log::error('FeedService@remove: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
    }

    public function comment($id, $user_id, $comment){
        try {

            DB::beginTransaction();

            $post = $this->postRepository->find($id);

            $commentModel = $post->comments()->create([
                'user_id' => $user_id,
                'body' => $comment
            ]);
            $commentId = $commentModel->id;

            DB::commit();

            return $commentId;

        }catch (\Exception $e){
            Log::error('FeedService@comment: ' . $e->getMessage());
            DB::rollBack();
            return false;
        }
    }

    public function getComments($request, $skip, $take){
        try {
            return $this->postRepository->find($request->id)->comments()
                ->orderBy('created_at', 'desc')
                ->offset($skip)
                ->limit($take)
                ->get();

        }catch (\Exception $e){
            Log::error('FeedService@getCommentsSkip: ' . $e->getMessage());
            return null;
        }
    }

    public function getPaginateComments($request){
        try {
            $page = $request->input('page', 1);
            $count = $request->input('count', 10);

            return $this->postRepository->find($request->id)->comments()
                ->orderBy('created_at', 'desc')
                ->paginate($count, ['*'], 'page', $page);

        }catch (\Exception $e){
            Log::error('FeedService@getCommentsSkip: ' . $e->getMessage());
            return null;
        }
    }
}
