<?php

namespace Orwallet\DixonpaySdk\Facade;

use Illuminate\Support\Facades\Facade;

class Dixonpay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return "dixonpay-sdk";
    }
}
