<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Trophy\StoreTrophyRequest;
use App\Http\Requests\Admin\AssignmentOfTrophy\UpdateTrophyRequest;
use App\Models\Trophy;
use App\Services\Admin\AdminTrophyService;
use App\Services\Admin\AdminUserService;
use App\Services\Api\BadgeService;
use Illuminate\Http\Request;

class AdminAssignTrophyController extends Controller
{

    public function __construct(private readonly AdminUserService $adminUserService,
                                private readonly AdminTrophyService $adminTrophyService)
    {
    }

    public function index()
    {
        return view('admin.assignment-of-trophies.index', [
            'users' => $this->adminUserService->getAllUsers(),
            'trophies' => $this->adminTrophyService->getAllTrophies(),
        ]);
    }

    public function edit($id)
    {
        return response()->json([
                'model' => $this->adminUserService->findByIdWithTrophies($id),
                'update_url' => route('admin.assignment-of-trophies.update', $id),
            ]
        );
    }

    public function update(UpdateTrophyRequest $request, $id)
    {
        $this->adminUserService->updateUserTrophies($request->validated(), $id);
        return redirect()->back();
    }


}
