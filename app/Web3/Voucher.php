<?php

namespace App\Web3;

final readonly class Voucher
{
    public function __construct(
        public string $categoryId,
        public string $signature,
    )
    {
    }

    public static function create(string $categoryId, string $signature): self
    {
        return new self($categoryId, $signature);
    }
}