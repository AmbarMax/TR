<?php

namespace App\Http\Requests\Api\Validate;

use Illuminate\Foundation\Http\FormRequest;

class CreateAchievementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'proof' => 'required|in:1,2',
            'proofUserId' => 'nullable',
        ];
    }
}
