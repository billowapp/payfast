<?php

namespace Billow;


use Illuminate\Support\ServiceProvider;

class PayfastServiceProvider extends ServiceProvider
{

    public function register()
    {
        // TODO: Implement register() method.
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/src/config/payfast.php' => config_path('courier.php'),
        ]);
    }


}