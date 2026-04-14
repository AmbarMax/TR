<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace App\Web3\Packages\web3\Methods\Personal;
use App\Web3\Packages\web3\Formatters\AddressFormatter;
use App\Web3\Packages\web3\Formatters\NumberFormatter;
use App\Web3\Packages\web3\Formatters\StringFormatter;
use App\Web3\Packages\web3\Methods\EthMethod;
use App\Web3\Packages\web3\Validators\AddressValidator;
use App\Web3\Packages\web3\Validators\QuantityValidator;
use App\Web3\Packages\web3\Validators\StringValidator;

class UnlockAccount extends EthMethod
{
    /**
     * validators
     * 
     * @var array
     */
    protected $validators = [
        AddressValidator::class, StringValidator::class, QuantityValidator::class
    ];

    /**
     * inputFormatters
     * 
     * @var array
     */
    protected $inputFormatters = [
        AddressFormatter::class, StringFormatter::class, NumberFormatter::class
    ];

    /**
     * outputFormatters
     * 
     * @var array
     */
    protected $outputFormatters = [];

    /**
     * defaultValues
     * 
     * @var array
     */
    protected $defaultValues = [
        2 => 300
    ];
}