<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Trophy\StoreTrophyRequest;
use App\Http\Requests\Admin\Trophy\UpdateTrophyRequest;
use App\Services\Admin\AdminItemService;
use Illuminate\Http\Request;

class AdminItemController extends Controller
{

    public function __construct(private readonly AdminItemService $adminItemService)
    {
    }

    public function index()
    {
        return view('admin.items.index', [
            'items' => $this->adminItemService->getAll()
        ]);
    }

    public function show(Request $request, string $id)
    {
        return response()->json([
                'model' => $this->adminItemService->show($id),
            ]
        );
    }

    public function store(Request $request)
    {
        $this->adminItemService->store(
            $request->except('_token')
        )? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Item successfully created'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Item not created'
            ]);
        return redirect()->back();
    }

    public function delete(Request $request, string $id)
    {
        $this->adminItemService->delete($id)? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Item successfully deleted'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Item not deleted'
            ]);
        return redirect()->back();
    }

    public function update(Request $request, string $id){
        $this->adminItemService->update($request->toArray(), $id)? $request->session()->flash('notification' ,[
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
