<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Enums\SessionStatus;
use App\Http\Requests\Admin\Balance\UpdateBalanceRequest;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\Request;

class AdminBalanceController
{
    public function __construct(private readonly AdminUserService $adminUserService)
    {
    }

    public function index()
    {
        return view('admin.balances.index',[
            'users' => $this->adminUserService->findAllWithBalance()
        ]);
    }

    public function update(UpdateBalanceRequest $request,  $id){
        $this->adminUserService->updateUserBalance($id, $request->except('_token'))?
            $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'User balance updated'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'User balance not updated'
            ]);
        return redirect()->back();
    }

    public function edit(Request $request, $id)
    {
        return response()->json([
                'model' => $this->adminUserService->findByIdWithBalance($id),
                'update_url' => route('admin.balances.update', $id),
            ]
        );
    }

}
