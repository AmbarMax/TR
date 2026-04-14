<?php

namespace App\Http\Controllers\Admin\Dashboard;
use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AdminController extends Controller
{

    public function index()
    {
        return view('admin.admins.index', [
            'users' => Admin::all(),
        ]);
    }

    public function store(StoreAdminRequest $request)
    {
        try {
            Admin::query()->create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password'=> Hash::make($request->password),
                    'super_admin'=> $request->filled('super_admin'),
                ])? $request->session()->flash('notification' ,[
                'type' => SessionStatus::Success,
                'title' => 'Success',
                'message' => 'Admin successfully created'
            ]):
                $request->session()->flash('notification' ,[
                    'type' => SessionStatus::Error,
                    'title' => 'Error',
                    'message' => 'Admin not created'
                ]);
        }catch (\Exception $exception){
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Admin not created'
            ]);
            Log::error($exception->getMessage());
        }
        return redirect()->back();
    }

    public function edit(Request $request, $id)
    {
        return response()->json([
                'model' => Admin::query()->find($id),
                'update_url' => route('admin.admins.update', $id),
            ]
        );
    }


    public function update(UpdateAdminRequest $request, $id)
    {
        if (Auth::id() == $id && !$request->filled('super_admin')){
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => "You can't remove yourself from super admins"
            ]);
            return redirect()->back();
        }
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'super_admin' => $request->filled('super_admin'),
        ];
        if (!is_null($request->password) && trim($request->password) != '') {
            $updateData['password'] = Hash::make($request->password);
        }
        try {
            Admin::query()->find($id)
                ->update($updateData)
                ? $request->session()->flash('notification' ,[
                'type' => SessionStatus::Success,
                'title' => 'Success',
                'message' => 'Admin successfully updated'
            ]):
                $request->session()->flash('notification' ,[
                    'type' => SessionStatus::Error,
                    'title' => 'Error',
                    'message' => 'Admin not updated'
                ]);
        }catch (\Exception $exception){
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Admin not updated'
            ]);
            Log::error($exception->getMessage());
        }

        return redirect()->back();
    }

    public function delete(Request $request, $id)
    {
        if (Auth::id() == $id){
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => "You can't delete yourself"
            ]);
        } else {
            try {
                Admin::query()->find($id)->delete()
                    ?$request->session()->flash('notification' ,[
                    'type' => SessionStatus::Success,
                    'title' => 'Success',
                    'message' => 'Admin successfully deleted'
                ]):
                    $request->session()->flash('notification' ,[
                        'type' => SessionStatus::Error,
                        'title' => 'Error',
                        'message' => 'Admin not deleted'
                    ]);
            }catch (\Exception $exception){
                $request->session()->flash('notification' ,[
                    'type' => SessionStatus::Error,
                    'title' => 'Error',
                    'message' => 'Admin not deleted'
                ]);
                Log::error($exception->getMessage());
            }
        }
        return redirect()->back();
    }

}
