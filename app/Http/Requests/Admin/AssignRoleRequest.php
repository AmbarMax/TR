<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class AssignRoleRequest extends FormRequest
{
    /**
     * Defense in depth — only tr_superadmin can assign the tr_superadmin
     * role. The route itself is already gated by tr_admin|tr_superadmin
     * middleware, so we only need to filter the super-specific case here.
     */
    public function authorize(): bool
    {
        if ($this->input('role') === 'tr_superadmin') {
            return Auth::user()?->hasRole('tr_superadmin') ?? false;
        }
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'in:tr_moderator,tr_admin,tr_superadmin'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Only tr_moderator, tr_admin, and tr_superadmin can be assigned via this endpoint.',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Only tr_superadmin can assign the tr_superadmin role.',
            ], 403)
        );
    }
}
