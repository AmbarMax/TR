<?php

namespace App\Http\Requests\Admin\Trophy;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTrophyRequest extends FormRequest
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
            'image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'badges' => 'required|array',
            'badges.*' => 'required|exists:badges,id',
            'description' => 'string|nullable',
            'is_nft' => 'string|nullable',
            'max_supply' => 'numeric|required_if:is_nft,on|min:1|max:1000000000',
        ];
    }

}
