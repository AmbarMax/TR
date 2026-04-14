<?php

namespace App\Web3;

use App\Web3\Packages\web3\Eth;
use Illuminate\Support\Facades\Redis;

final readonly class Nonce
{
    private const NONCE_KEY = 'nonce:%s';

    public function __construct(public int $value)
    {
    }

    public static function get(Eth $eth, Address $address): int
    {
        $result = 0;
        $eth->getTransactionCount($address->value, 'pending', function ($err, $nonce) use (&$result) {
            if ($err !== null) {
                echo 'Error: ' . $err->getMessage();
                throw new \InvalidArgumentException('Error retrieving nonce');
            }
            $result = $nonce->toString();
        });

        return intval($result);
    }

    public static function set(int $nonce, Address $address): void
    {
        Redis::del($key = sprintf(self::NONCE_KEY, $address->value));
        Redis::set($key, $nonce);
    }
}