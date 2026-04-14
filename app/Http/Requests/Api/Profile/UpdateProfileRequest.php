<?php

namespace App\Http\Requests\Api\Profile;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'string',
            'email' => 'email|string',
            'username' => 'string',
            'phone_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'email_verified_at' => 'nullable|date',
            'password' => 'nullable|string',
            'description' => 'nullable|string',
            'country_id' => 'nullable|integer',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'date_of_birth' => $this->date_of_birth != null ? Carbon::parse($this->date_of_birth) : $this->date_of_birth,
            'email_verified_at' => $this->email_verified_at != null ? Carbon::parse($this->email_verified_at) : $this->email_verified_at,
        ]);
    }
}
