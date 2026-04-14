<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace App\Web3\Packages\web3\Methods\Eth;
use App\Web3\Packages\web3\Formatters\AddressFormatter;
use App\Web3\Packages\web3\Formatters\HexFormatter;
use App\Web3\Packages\web3\Methods\EthMethod;
use App\Web3\Packages\web3\Validators\AddressValidator;
use App\Web3\Packages\web3\Validators\HexValidator;

class Sign extends EthMethod
{
    /**
     * validators
     * 
     * @var array
     */
    protected $validators = [
        AddressValidator::class, HexValidator::class
    ];

    /**
     * inputFormatters
     * 
     * @var array
     */
    protected $inputFormatters = [
        AddressFormatter::class, HexFormatter::class
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
    protected $defaultValues = [];
}