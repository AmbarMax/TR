<?php

namespace App\Http\Controllers\Api\Validate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Validate\AchievementActionRequest;
use App\Http\Requests\Api\Validate\CreateAchievementRequest;
use App\Http\Requests\Api\Validate\SocialApproveRequest;
use App\Models\Achievement;
use App\Services\Api\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class AchievementController extends Controller
{
    public function __construct(private readonly AchievementService $achievementService){}

    public function achievementCreate(CreateAchievementRequest $request): JsonResponse
    {
        $achievement = $this->achievementService->create($request);

        return response()->json(['message' => $achievement['message']], $achievement['status']);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->achievementService->getAchievements($request));
    }

    public function delete(AchievementActionRequest $request): JsonResponse
    {
        $delete = $this->achievementService->delete($request);

        return response()->json(['message' => $delete['message']], $delete['status']);
    }

    public function share(AchievementActionRequest $request): JsonResponse
    {
        $share = $this->achievementService->share($request);

        return response()->json(['message' => $share['message']], $share['status']);
    }

    public function reject(AchievementActionRequest $request): JsonResponse
    {
        $reject = $this->achievementService->reject($request);

        return response()->json(['message' => $reject['message']], $reject['status']);
    }

    public function socialApprove(SocialApproveRequest $request): JsonResponse
    {
        $approve = $this->achievementService->approve($request);

        return response()->json(['message' => $approve['message']], $approve['status']);
    }

    public function socialApproveReject(Request $request): JsonResponse
    {
        $rejectSocialApprove = $this->achievementService->socialApproveReject($request);

        return response()->json(['message' => $rejectSocialApprove['message']], $rejectSocialApprove['status']);
    }

    public function showcase($id): JsonResponse
    {
        return $this->achievementService->showcase($id)?
            response()->json([
                'message' => 'Achievement has been successfully added to the virtual hall',
                'type' => 'success'
            ], ResponseAlias::HTTP_OK):
            response()->json([
                'message' => 'Achievement not found',
                'type' => 'error'
            ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function info($id): JsonResponse
    {
        return response()->json(Achievement::where('id', $id)->first());
    }

    public function removeShowcase($id): JsonResponse
    {
        return $this->achievementService->removeShowcase($id)?
            response()->json([
                'message' => 'Achievement has been successfully removed from the virtual hall',
                'type' => 'success'
            ], ResponseAlias::HTTP_OK):
            response()->json([
                'message' => 'Achievement not found',
                'type' => 'error'
            ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function revalidate(Request $request): JsonResponse
    {
        $revalidate = $this->achievementService->revalidate($request);

        return response()->json(['message' => $revalidate['message']], $revalidate['status']);
    }

    public function revalidateSocial(Request $request): JsonResponse
    {
        $revalidate = $this->achievementService->revalidateSocial($request);

        return response()->json(['message' => $revalidate['message']], $revalidate['status']);
    }
}
