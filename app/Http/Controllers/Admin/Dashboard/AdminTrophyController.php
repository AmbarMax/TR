<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Trophy\StoreTrophyRequest;
use App\Http\Requests\Admin\Trophy\UpdateTrophyRequest;
use App\Services\Admin\AdminKeyService;
use App\Services\Admin\AdminTrophyService;
use App\Services\Api\BadgeService;
use Illuminate\Http\Request;

class AdminTrophyController extends Controller
{

    public function __construct(private readonly AdminTrophyService $adminTrophyService,
                                private readonly BadgeService $badgeService,
                                private readonly AdminKeyService $adminKeyService)
    {
    }

    public function index()
    {
        return view('admin.trophies.index', [
            'badges' => $this->badgeService->getAll(),
            'trophies' => $this->adminTrophyService->getAllTrophies(),
            'keys' => $this->adminKeyService->getAll(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        return response()->json([
                'model' => $this->adminTrophyService->getTrophy($id),
            ]
        );
    }

    public function store(StoreTrophyRequest $request)
    {
        $this->adminTrophyService->store(
            $request->except('_token')
        )? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Trophy successfully created'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Trophy not created'
            ]);
        return redirect()->back();
    }

    public function delete(Request $request, string $id)
    {
        $this->adminTrophyService->remove($id)? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Trophy successfully deleted'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Trophy not deleted'
            ]);
        return redirect()->back();
    }

    public function update(Request $request, string $id){
        $this->adminTrophyService->update($request->toArray(), $id)? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Item successfully updated'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Item not updated'
            ]);
        return redirect()->back();
    }


}
