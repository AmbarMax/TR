<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Trophy\StoreTrophyRequest;
use App\Http\Requests\Admin\Trophy\UpdateTrophyRequest;
use App\Services\Admin\AdminItemService;
use App\Services\Admin\AdminKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminKeyController extends Controller
{

    public function __construct(private readonly AdminKeyService $adminKeyService)
    {
    }

    public function index()
    {
        return view('admin.keys.index', [
            'keys' => $this->adminKeyService->getAll()
        ]);
    }

    public function show(Request $request, string $id)
    {
        return response()->json([
                'model' => $this->adminKeyService->show($id),
            ]
        );
    }

    public function store(Request $request)
    {
        $this->adminKeyService->store(
            $request->except('_token')
        )? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Key successfully created'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Key not created'
            ]);
        return redirect()->back();
    }

    public function delete(Request $request, string $id)
    {
        $this->adminKeyService->delete($id)? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Key successfully deleted'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Key not deleted'
            ]);
        return redirect()->back();
    }

    public function edit($id)
    {
        return response()->json([
                'model' => $this->adminKeyService->show($id),
                'update_url' => route('admin.keys.update', $id),
            ]
        );
    }

    public function update(Request $request, string $id){
        $this->adminKeyService->update($request->toArray(), $id)? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Key successfully updated'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Key not updated'
            ]);
        return redirect()->back();
    }


}
