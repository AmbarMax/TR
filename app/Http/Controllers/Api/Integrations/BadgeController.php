<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Http\Resources\BadgeResource;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Post;
use App\Models\User;
use App\Services\Api\AchievementService;
use App\Services\Api\BadgeService;
use App\Services\Api\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BadgeController
{
    public function __construct(private readonly BalanceService $balanceService)
    {
    }

    public function badges(){
        return BadgeResource::collection(Auth::user()->badges()->get())
            ->response()->getData(true);
    }

    public function publicBadges(){
        return BadgeResource::collection(Auth::user()->publicBadges()->get())
            ->response()->getData(true);
    }

    public function sync($id, $achievements){
        $data = json_decode($achievements);
        $user = User::find($id);
        $this->balanceService->increaseBalanceForImportBadges(
            $user,
            count($user->badges()->syncWithoutDetaching($data)['attached'])
        );
        return redirect()->route('ambar', ['any' => '/trophy-room']);
    }

    public function share(Request $request)
    {
        try {
            DB::beginTransaction();
            $badge = Badge::find($request->id);
            $badge->users()->updateExistingPivot(Auth::id(), ['is_share' => true]);
            if ($badge->post()->where('user_id', Auth::id())->doesntExist())
            {
                $badge->post()->create([
                    'user_id' => Auth::id()
                ]);
            }
            DB::commit();
            return response()->json([
                'message' => 'Badges successfully shared'
            ], ResponseAlias::HTTP_OK);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Badges not found'
            ], ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    public function destroy($id){
        Auth::user()->badges()->detach($id);
        return BadgeResource::collection(Auth::user()->publicBadges()->get())
            ->response()->getData(true);
    }

    public function showcase($id){
        Badge::find($id)->users()->updateExistingPivot(Auth::id(), ['display' => true]);
        return response()->json([
            'message' => 'Badges successfully showcase'
        ], ResponseAlias::HTTP_OK);
    }

    public function remove($id){
        Badge::find($id)->users()->updateExistingPivot(Auth::id(), ['display' => false]);
        return response()->json([
            'message' => 'Badges successfully remove'
        ], ResponseAlias::HTTP_OK);
    }
}
