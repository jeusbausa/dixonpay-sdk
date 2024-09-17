<?php

use Illuminate\Support\ServiceProvider;

class DixonpayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind("dixonpay", function (mixed $param) {
            return new DixonPay($param);
        });
    }

    public function register()
    {
        $this->publishes([__DIR__ . "/../config/dixonpay.php" => config_path("dixonpay.php")]);
    }
}
