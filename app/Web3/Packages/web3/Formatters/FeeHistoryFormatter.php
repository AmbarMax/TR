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
use App\Web3\Packages\web3\Formatters\BigNumberFormatter;
use App\Web3\Packages\web3\Formatters\IFormatter;
use App\Web3\Packages\web3\Utils;

class FeeHistoryFormatter implements IFormatter
{
    /**
     * format
     * 
     * @param mixed $value
     * @return string
     */
    public static function format($value)
    {
        if (isset($value->oldestBlock)) {
            $value->oldestBlock = BigNumberFormatter::format($value->oldestBlock);
        }
        if (isset($value->baseFeePerGas)) {
            foreach ($value->baseFeePerGas as $key => $baseFeePerGas) {
                $value->baseFeePerGas[$key] = BigNumberFormatter::format($baseFeePerGas);
            }
        }
        if (isset($value->reward)) {
            foreach ($value->reward as $keyOut => $rewards) {
                foreach ($rewards as $keyIn => $reward) {
                    $value->reward[$keyOut][$keyIn] = BigNumberFormatter::format($reward);
                }
            }
        }
        return $value;
    }
}