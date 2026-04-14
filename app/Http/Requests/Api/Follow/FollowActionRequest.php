<?php

namespace App\Http\Requests\Api\Follow;

use Illuminate\Foundation\Http\FormRequest;

class FollowActionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:users,id',
        ];
    }
}