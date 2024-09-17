<?php

namespace Orwallet\DixonpaySdk;

use Orwallet\DixonpaySdk\Dixonpay;
use Illuminate\Support\ServiceProvider;

class DixonpayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind("dixonpay-sdk", function (mixed $param) {
            return new Dixonpay($param);
        });
    }
}
