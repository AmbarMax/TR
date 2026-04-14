<?php

namespace App\Http\Requests\Api\Validate;

use Illuminate\Foundation\Http\FormRequest;

class SocialApproveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'id' => 'required|exists:achievements,id',
        ];
    }
}