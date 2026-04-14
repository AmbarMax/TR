<?php

namespace App\Http\Controllers\Admin\Dashboard;
use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;


class AdminUserController extends Controller
{

    public function __construct(private readonly AdminUserService $adminUserService)
    {
    }

    public function index()
    {
        return view('admin.users.index', [
            'users' => $this->adminUserService->getAllUsers(),
            'roles' => Role::get(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->adminUserService->store(
            $request->except('_token')
        )? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'User successfully created'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'User not created'
            ]);
        return redirect()->back();
    }

    public function edit(Request $request, $id)
    {
        return response()->json([
                'model' => $this->adminUserService->getById($id),
                'update_url' => route('admin.users.update', $id),
            ]
        );
    }

    public function show(Request $request, $id)
    {
        return response()->json([
                'model' => $this->adminUserService->getById($id),
            ]
        );
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $this->adminUserService->update(
            $id,
            $request->except('_token')
        )? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'User successfully updated'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'User not updated'
            ]);
        return redirect()->back();
    }

    public function delete(Request $request, $id)
    {
        $this->adminUserService->delete($id)
        ?$request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'User successfully deleted'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'User not deleted'
            ]);

        return redirect()->back();
    }

    public function disable2fa($id)
    {
        User::where('id', $id)->update([
            'google2fa_secret' => null,
            'google2fa_status' => false
        ]);
    }

}
