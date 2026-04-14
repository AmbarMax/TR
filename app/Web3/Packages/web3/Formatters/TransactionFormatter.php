<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace App\Web3\Packages\web3\Formatters;
use App\Web3\Packages\web3\Formatters\HexFormatter;
use App\Web3\Packages\web3\Formatters\IFormatter;
use App\Web3\Packages\web3\Formatters\QuantityFormatter;
use App\Web3\Packages\web3\Utils;

class TransactionFormatter implements IFormatter
{
    /**
     * format
     * 
     * @param mixed $value
     * @return string
     */
    public static function format($value)
    {
        if (isset($value['gas'])) {
            $value['gas'] = QuantityFormatter::format($value['gas']);
        }
        if (isset($value['gasPrice'])) {
            $value['gasPrice'] = QuantityFormatter::format($value['gasPrice']);
        }
        if (isset($value['value'])) {
            $value['value'] = QuantityFormatter::format($value['value']);
        }
        if (isset($value['data'])) {
            $value['data'] = HexFormatter::format($value['data']);
        }
        if (isset($value['nonce'])) {
            $value['nonce'] = QuantityFormatter::format($value['nonce']);
        }
        return $value;
    }
}