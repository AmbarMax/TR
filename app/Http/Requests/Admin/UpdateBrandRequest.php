<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is already gated by role:tr_admin|tr_superadmin middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'accent_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'tagline'      => ['nullable', 'string', 'max:80'],
            'verified_at'  => ['nullable', 'date'],
            'is_featured'  => ['nullable', 'boolean'],
            'banner'       => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'accent_color.regex' => 'Accent color must be a 6-digit hex value (e.g. #ff6100).',
            'tagline.max'        => 'Tagline must be 80 characters or less.',
        ];
    }
}
