<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route is gated by role:tr_admin|tr_superadmin middleware.
    }

    public function rules(): array
    {
        return [
            'log_name' => ['nullable', 'string', 'in:default,trophy,badge,role,user,poll,event,guild,badge_rule,chest,key'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'cursor'   => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'log_name.in' => 'Unknown log_name filter. Allowed: default, trophy, badge, role, user, poll, event, guild, badge_rule, chest, key.',
        ];
    }
}
