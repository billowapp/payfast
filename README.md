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
    
    Class PaymentController extends Controller
    {
        protected $payfast;
        
        public function __construct(PaymentProcessor $payfast)
        {
            $this->payfast = $payfast;
        }
        
        public function confirmPayment()
        {
            // At this point the order should be created and most likely set to a status of pending.    
    
            $amount = 99.99; // Cart Total - Example Data.
            $merchant_reference = 001 // Order Reference - Example Data.
    
            $this->payfast->setBuyer('warren', 'hansen', 'sbtm01@payfast.co.za');
            $this->payfast->setAmount($order->amount);
            $this->payfast->setItem('urn', 'urn description');
            $this->payfast->setMerchantReference($order->m_payment_id);
    
            return $this->payfast->paymentForm(false);
        }
            
    }
    
```    
