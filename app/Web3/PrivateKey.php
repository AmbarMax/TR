<?php

namespace App\Web3;

final readonly class PrivateKey
{
    public function __construct(public string $value)
    {
        if (!(preg_match('/^(0x)?[a-fA-F0-9]+$/', $value) === 1)) {
            throw new \InvalidArgumentException('Invalid private key');
        }
    }

    public static function from(mixed $value): self
    {
        return new self(strval($value));
    }
}