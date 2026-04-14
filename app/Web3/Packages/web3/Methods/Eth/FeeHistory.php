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
use App\Web3\Packages\web3\Formatters\FeeHistoryFormatter;
use App\Web3\Packages\web3\Formatters\OptionalQuantityFormatter;
use App\Web3\Packages\web3\Formatters\QuantityFormatter;
use App\Web3\Packages\web3\Methods\EthMethod;
use App\Web3\Packages\web3\Validators\ArrayNumberValidator;
use App\Web3\Packages\web3\Validators\QuantityValidator;
use App\Web3\Packages\web3\Validators\TagValidator;

class FeeHistory extends EthMethod
{
    /**
     * validators
     * 
     * @var array
     */
    protected $validators = [
        QuantityValidator::class, [
            TagValidator::class, QuantityValidator::class
        ], ArrayNumberValidator::class
    ];

    /**
     * inputFormatters
     * 
     * @var array
     */
    protected $inputFormatters = [
        QuantityFormatter::class, OptionalQuantityFormatter::class
    ];

    /**
     * outputFormatters
     * 
     * @var array
     */
    protected $outputFormatters = [
        FeeHistoryFormatter::class
    ];

    /**
     * defaultValues
     * 
     * @var array
     */
    protected $defaultValues = [];
}