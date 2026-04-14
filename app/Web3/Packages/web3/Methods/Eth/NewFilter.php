<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

use App\Web3\Packages\web3\Eth;

use App\Web3\Packages\web3\Methods\EthMethod;
use App\Web3\Packages\web3\Validators\FilterValidator;

class NewFilter extends EthMethod
{
    /**
     * validators
     * 
     * @var array
     */
    protected $validators = [
        FilterValidator::class
    ];

    /**
     * inputFormatters
     * 
     * @var array
     */
    protected $inputFormatters = [];

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