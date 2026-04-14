<?php

namespace App\Http\Requests\Api\Validate;

use Illuminate\Foundation\Http\FormRequest;

class AchievementActionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:achievements,id',
        ];
    }
}