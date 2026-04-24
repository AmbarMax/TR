<?php

namespace App\Http\Requests\Admin\User;

use App\Rules\UsernameNotReserved;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserRequest extends FormRequest
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
            'email' => 'required|email|string',
            'username' => ['sometimes', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:users,username,'.$this->route('id'), new UsernameNotReserved()],
            'phone_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'email_verified_at' => 'nullable|date',
            'password' => 'nullable|string',
            'avatar' => 'nullable|image',
            'background' => 'nullable|image',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->request->has('avatar')){
            $this->merge([
                'avatar' => null,
            ]);
        }
        if (!$this->request->has('background')){
            $this->merge([
                'background' => null,
            ]);
        }
        $this->merge([
            'date_of_birth' => Carbon::parse($this->date_of_birth),
            'email_verified_at' => Carbon::parse($this->email_verified_at),
        ]);
    }
}
