<?php

namespace App\Http\Controllers\Api\Forge;

use App\Models\Chest;
use App\Models\Key;
use App\Models\Trophy;
use App\Models\User;
use App\Services\Api\ChestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ChestController
{

    public function __construct(
        private readonly ChestService $chestService
    )
    {
    }

    public function index()
    {
        return response()->json([
            'chests' => $this->chestService->index(),
            'keys' => $this->chestService->getUserTokenIds(),
        ], ResponseAlias::HTTP_OK);
    }

    public function userChests()
    {
        return response()->json([
            'chests' => $this->chestService->userChests(Auth::id()),
        ], ResponseAlias::HTTP_OK);
    }

    public function get($id, Request $request)
    {
        return $this->chestService->get($id, $request->input('token_id'))
            ?response()->json([
                'message' => 'Key selected!'
            ], ResponseAlias::HTTP_OK)
            :response()->json([
                'message' => 'Something went wrong. Try again later'
            ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function open($id)
    {
        return $this->chestService->open($id)
            ?response()->json([
                'message' => 'Chest opened'
            ], ResponseAlias::HTTP_OK)
            :response()->json([
                'message' => 'Something went wrong. Try again later'
            ], ResponseAlias::HTTP_BAD_REQUEST);

    }

    public function view($id)
    {
        $chest = Chest::with('items')->find($id);
        return $chest
            ?response()->json([
                'chest' => $chest,
            ], ResponseAlias::HTTP_OK)
            :response()->json([
                'message' => 'Something went wrong. Try again later'
            ], ResponseAlias::HTTP_BAD_REQUEST);

    }

}
