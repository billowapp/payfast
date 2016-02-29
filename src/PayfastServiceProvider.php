<?php

namespace Billow;


use Illuminate\Support\ServiceProvider;

class PayfastServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('Billow\Contracts\PaymentProcessor', 'Billow\Payfast');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/payfast.php' => config_path('payfast.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/config/payfast.php', 'payfast'
        );
    }


}