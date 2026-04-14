<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace App\Web3\Packages\web3\Methods\Shh;

use App\Web3\Packages\web3\Formatters\QuantityFormatter;
use App\Web3\Packages\web3\Methods\EthMethod;
use App\Web3\Packages\web3\Validators\QuantityValidator;

class UninstallFilter extends EthMethod
{
    /**
     * validators
     * 
     * @var array
     */
    protected $validators = [
        QuantityValidator::class
    ];

    /**
     * inputFormatters
     * 
     * @var array
     */
    protected $inputFormatters = [
        QuantityFormatter::class
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