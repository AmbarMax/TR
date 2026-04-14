<?php

namespace App\Web3;
use Illuminate\Support\Facades\Log;
final readonly class Address
{
    public function __construct(public string $value)
    {
        Log::info($value);
        if (!(preg_match('/^(0x)?[a-fA-F0-9]{40}$/', $value) === 1)) {
            throw new \InvalidArgumentException('Invalid address');
        }
    }

    public static function from(mixed $value): self
    {
        return new self(strval($value));
    }
}
