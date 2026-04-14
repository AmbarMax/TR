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
use App\Web3\Packages\web3\Formatters\IFormatter;
use App\Web3\Packages\web3\Utils;

class NumberFormatter implements IFormatter
{
    /**
     * format
     * 
     * @param int|float $value
     * @return int|float
     */
    public static function format($value)
    {
        return $value;
    }
}