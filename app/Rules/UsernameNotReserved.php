<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UsernameNotReserved implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return; // other rules (string/required) surface that problem
        }

        $normalized = strtolower(trim($value));

        if (DB::table('reserved_usernames')->where('username', $normalized)->exists()) {
            $fail('The :attribute is reserved and cannot be used.');
        }
    }
}
