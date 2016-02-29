# Laravel5 Payfast

A dead simple Laravel 5.2 payment processing class for payments through payfast.co.za. This package only supports ITN transactions. Laravel5 Payfast is strictly use at own risk.

## Installation

Add Laravel5 Payfast to your composer.json


    composer require billowapp/payfast


Add the PayfastServiceProvider to your providers array in config/app.php

```php
    'providers' => [
        //
        
        'Billow\PayfastServiceProvider'
    ];
```    
### Config
publish default configuration file.

    php artisan vendor:publish

### Usage

```php
    <?php
    
    use Billow\Contracts\PaymentProcessor;
    
    protected $payfast;
    
    public function __construct(PaymentProcessor $payfast)
    {
        $this->payfast = $payfast;
    }
```    
