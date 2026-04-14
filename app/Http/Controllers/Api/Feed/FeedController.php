<?php

namespace App\Http\Controllers\Api\Feed;

use App\Enums\AvatarType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Post\FeedRequest;
use App\Http\Requests\Api\Post\PostCommentsRequest;
use App\Http\Requests\Api\Post\PostDonationRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Api\UserRepository;
use App\Services\Api\BalanceService;
use App\Services\Api\FeedService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FeedController extends Controller
{
    public function __construct(private readonly FeedService    $feedService,
                                private readonly BalanceService $balanceService,
                                private readonly UserRepository $userRepository)
    {
    }

    public function index(FeedRequest $request){

        $posts = $this->feedService->getFeed($request->skip, $request->take);

        if ($posts){
            return PostResource::collection($posts)->response()->getData(true);
        }
        return response()->json(null);
    }

    public function share(Request $request){
        return $this->feedService->share($request->id, Auth::id(), $request->type) ?
            response()->json([
                'message' => 'Trophy successfully shared',
                'type' => 'success'
            ], ResponseAlias::HTTP_OK) :
            response()->json([
                'message' => 'Trophy not found',
                'type' => 'error'
            ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function followingFeed(FeedRequest $request)
    {
        /** @var User $user */
        $user = User::where('id', $request->user()->id)->with('roles')->first();

        if ($user->hasRole(Role::MODERATOR)) {
            $posts = Post::orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $request->input('page', 1));
        } else {
            $posts = $this->feedService->getPosts($request);
        }

        if ($posts){
            return PostResource::collection($posts)->response()->getData(true);
        }
        return response()->json(null);
    }

    public function getMyFeed(Request $request)
    {
        $page = $request->input('page', 1);
        $count = $request->input('count', 10);

        $posts = $this->feedService->getUserPosts($request->user(), $page, $count);
        if ($posts){
            return PostResource::collection($posts)->response()->getData(true);
        }
        return response()->json(null);
    }

    public function donate(PostDonationRequest $request): JsonResponse
    {
        $donate = $this->balanceService->donateOnPost($request->input('id'), Auth::id(), $request->input('amount'));

        return response()->json(['message' => $donate['message']], $donate['status']);
    }

    public function remove(Request $request): JsonResponse
    {
        return $this->feedService->remove($request->id, $request->user()->id) ?
            response()->json([
                'message' => 'Post successfully removed',
                'type' => 'success'
            ], ResponseAlias::HTTP_OK) :
            response()->json([
                'message' => 'Post not found',
                'type' => 'error'
            ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function createComment(Request $request): JsonResponse
    {
        $comment = $this->feedService->comment($request->id, $request->user()->id, $request->input('comment'));

        if ($comment) {
            return response()->json([
                'message' => 'Comment successfully',
                'comment' => $comment,
                'type' => 'success'
            ], ResponseAlias::HTTP_OK);
        } else{
            return response()->json([
                'message' => 'Comment failed',
                'type' => 'error'
            ], ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    public function getPostComments(PostCommentsRequest $request, $id)
    {
        $comments = $this->feedService->getComments($id, $request->skip, $request->take);
        if ($comments){
            return CommentResource::collection($comments)->response()->getData(true);
        }
        return response()->json(null);
    }

    public function getComments(PostCommentsRequest $request, $id)
    {
        $comments = $this->feedService->getPaginateComments($request);
        if ($comments){
            return CommentResource::collection($comments)->response()->getData(true);
        }
        return response()->json(null);
    }

    /** Deleting post by moderator */
    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = User::where('id', $request->user()->id)->with('roles')->first();

        if ($user->hasRole(Role::MODERATOR)) {

            if (!$request->input('id')) {
               return response()->json([
                    'message' => 'Post id required',
                    'type' => 'error'
                ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }

            $post = Post::where('id', $request->input('id'))->first();

            if (!$post) {
                return response()->json([
                    'message' => 'Post not found',
                    'type' => 'error'
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            $post->delete();

            return response()->json(['message' => 'Post deleted by moderator success']);

        } else {
            return response()->json([
                'message' => 'You do not have permission to delete a post',
                'type' => 'error'
            ], ResponseAlias::HTTP_FORBIDDEN);
        }
    }

    public function destroyComment(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = User::where('id', $request->user()->id)->with('roles')->first();

        if ($user->hasRole(Role::MODERATOR)) {

            if (!$request->input('id')) {
                return response()->json([
                    'message' => 'Comment id required',
                    'type' => 'error'
                ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!$request->input('postId')) {
                return response()->json([
                    'message' => 'postId id required',
                    'type' => 'error'
                ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }

            $comment = Comment::where('id', $request->input('id'))->first();

            if (!$comment) {
                return response()->json([
                    'message' => 'Comment id required',
                    'type' => 'error'
                ], ResponseAlias::HTTP_NOT_FOUND);
            }

            $comment->delete();

            $post = Post::where('id', $request->input('postId'))->with('comments', 'comments.user:id,username,avatar')->first();

            $post2 = $post->toArray();
            foreach ($post2['comments'] as $index => &$comment){
                $comment['created_at'] = Carbon::parse($comment['created_at'])->format('F j, Y');
                $comment['user']['avatar'] = $post->comments[$index]->user->avatar ? $post->comments[$index]->user->getAvatarFile(AvatarType::Small()) : null;
            }

            return response()->json([
                'message' => 'Comment deleted by moderator success',
                'post' => $post2
            ]);

        } else {
            return response()->json([
                'message' => 'You do not have permission to delete a comment',
                'type' => 'error'
            ], ResponseAlias::HTTP_FORBIDDEN);
        }
    }

}
