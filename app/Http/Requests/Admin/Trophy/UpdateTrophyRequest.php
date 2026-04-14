<?php

namespace App\Http\Requests\Admin\Trophy;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTrophyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'receive' => 'required|numeric',
            'badges' => 'required|array',
            'badges.*' => 'required|exists:badges,id',
            'description' => 'string|nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ];
    }

}
