<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Trophy\UpdateTrophyRequest;
use App\Services\Admin\AdminExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminExchangeController extends Controller
{

    public function __construct(private readonly AdminExchangeService $adminExchangeService)
    {
    }

    public function index()
    {
        return view('admin.exchanges.index', [
            'exchanges' => $this->adminExchangeService->getAll()
        ]);
    }

    public function edit(Request $request, $id)
    {
        return response()->json([
                'model' => $this->adminExchangeService->getById($id),
                'update_url' => route('admin.exchanges.update', $id),
            ]
        );
    }


    public function update(Request $request, string $id){
        $this->adminExchangeService->update(
            $id,
            $request->except('_token')
        )? $request->session()->flash('notification' ,[
            'type' => SessionStatus::Success,
            'title' => 'Success',
            'message' => 'Exchange status successfully updated'
        ]):
            $request->session()->flash('notification' ,[
                'type' => SessionStatus::Error,
                'title' => 'Error',
                'message' => 'Exchange status not updated'
            ]);
        return redirect()->back();
    }


}
