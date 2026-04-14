<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Trophy\StoreTrophyRequest;
use App\Http\Requests\Admin\Trophy\UpdateTrophyRequest;
use App\Services\Admin\AdminChestService;
use App\Services\Admin\AdminItemService;
use App\Services\Admin\AdminKeyService;
use Illuminate\Http\Request;

class AdminChestController extends Controller
{

    public function __construct(private readonly AdminChestService $adminChestService,
                                private readonly AdminItemService $adminItemService,
                                private readonly AdminKeyService $adminKeyService)
    {
    }

    public function index()
    {
        return view('admin.chests.index', [
            'chests' => $this->adminChestService->getAll(),
            'items' => $this->adminItemService->getAll(),
            'keys' => $this->adminKeyService->getAll(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        return response()->json([
                'model' => $this->adminChestService->show($id),
            ]
        );
    }

    public function store(Request $request)
    {
        $this->adminChestService->store(
            $request->except('_token')
        )? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Chest successfully created'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Chest not created'
            ]);
        return redirect()->back();
    }

    public function edit($id)
    {
        return response()->json([
                'model' => $this->adminChestService->show($id),
                'update_url' => route('admin.chests.update', $id),
            ]
        );
    }

    public function delete(Request $request, string $id)
    {
        $this->adminChestService->delete($id)? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Chest successfully deleted'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Chest not deleted'
            ]);
        return redirect()->back();
    }

    public function update(Request $request, string $id){
        $this->adminChestService->update($request->toArray(), $id)? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Chest successfully updated'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Chest not updated'
            ]);
        return redirect()->back();
    }


}
