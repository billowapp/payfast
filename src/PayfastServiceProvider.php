<?php

namespace Billow;


use Illuminate\Support\ServiceProvider;

class PayfastServiceProvider extends ServiceProvider
{

    public function register()
    {

    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/payfast.php' => config_path('payfast.php'),
        ]);
    }


}