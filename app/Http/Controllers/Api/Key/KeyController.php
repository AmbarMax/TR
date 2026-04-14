<?php

namespace App\Http\Controllers\Api\Key;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Follow\FollowActionRequest;
use App\Http\Resources\ExchangeListResource;
use App\Models\User;
use App\Repositories\Api\FollowerRepository;
use App\Services\Api\ExchangeService;
use App\Services\Api\FollowService;
use App\Services\Api\KeyService;
use App\Services\Api\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class KeyController extends Controller
{

    public function __construct(private readonly KeyService $keyService){}

    public function index(Request $request)
    {
        return response()->json($this->keyService->index(Auth::id()), ResponseAlias::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->exchangeService->store(Auth::id(), $request->toArray())
            ? response()->json([], ResponseAlias::HTTP_OK)
            : response()->json([], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
    }
}
