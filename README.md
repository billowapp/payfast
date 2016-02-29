# Laravel5 Payfast

A dead simple Laravel 5.2 payment processing class for payments through payfast.co.za. This package only supports ITN transactions. Laravel5 Payfast is strictly use at own risk.

## Installation

    composer require billowapp/payfast

### Serivce Provider

    'providers' => [
        //
        
        'Billow\PayfastServiceProvider'
    ];
### Config
publish default configuration file.

    php artisan vendor:publish

### Usage

There are 2 ways to instiate the Class.

#### Option 1

    <?php
    
    use Billow\Contracts\PaymentProcessor;
    
    protected $payfast;
    
    public function __construct(PaymentProcessor $payfast)
    {
        $this->payfast = $payfast;
    }
