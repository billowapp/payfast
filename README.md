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
    
IMPORTANT: You will need to edit App\Http\Middleware\VerifyCsrfToken by adding the route, which handles the ITN response to the $excepted array. Validation is done via the ITN response.


    
```php

/*
|--------------------------------------------------------------------------
| Merchant Settings
|--------------------------------------------------------------------------
| All Merchant settings below are for example purposes only. for more info
| see www.payfast.co.za. The Merchant ID and Merchant Key can be obtained 
| from your payfast.co.za account.
|
*/
    
[

    'testing' => true, // Set to false when in production.

    'currency' => 'ZAR', // ZAR is the only supported currency at this point.

    'merchant' => [
        'merchant_id' => '10000100', // TEST Credentials. Replace with your merchant ID from Payfast.
        'merchant_key' => '46f0cd694581a', // TEST Credentials. Replace with your merchant key from Payfast.
        'return_url' => 'http://your-domain.co.za/success', // The URL the customer should be redirected to after a successful payment.
        'cancel_url' => 'http://your-domain.co.za/cancelled', // The URL the customer should be redirected to after a payment is cancelled.
        'notify_url' => 'http://your-domain.co.za/itn', // The URL to which Payfast will post return variables.
    ]
    
];

```
### Usage

Creating a payment returns an html form ready to POST to payfast. When the customer submits the form they will be redirected to payfast to complete payment. Upon successful payment the customer will be returned to the specified 'return_url' and in the case of a cancellation they will be returned to the specified 'cancel_url'

```php

use Billow\Contracts\PaymentProcessor;
    
Class PaymentController extends Controller
{
    public function confirmPayment(PaymentProcessor $payfast)
    {
        // Eloqunet example.  
        $cartTotal = 9999;
        $order = Order::create([
                'm_payment_id' => '001',
                'amount'       => $cartTotal     
            ]);
    
        // Build up payment Paramaters.
        $payfast->setBuyer('first name', 'last name', 'email');
        $payfast->setAmount($order->amount);
        $payfast->setItem('item-title', 'item-description');
        $payfast->setMerchantReference($order->m_payment_id);
    
        // Return the payment form.
        return $payfast->paymentForm('Place Order');
        
    }
            
}
```  

## ITN Responses


```php

use Billow\Contracts\PaymentProcessor;
    
Class PaymentController extends Controller
{
    public function itn(Request $request, PaymentProcessor $payfast)
    {
    
        // Retrieve the Order from persistance. Eloquent Example. 
        $order = Order::where('m_payment_id', $request->get('m_payment_id'))->firstOrFail(); // Eloquent Example 
    
        // Verify the payment status.
        $status = $payfast->verify($request, $order->amount, $order->m_payment_id)->status();
    
        // Handle the result of the transaction.
        switch( $status )
        {
            case 'COMPLETE': // Things went as planned, update your order status and notify the customer/admins.
                break;
            case 'FAILED': // We've got problems, notify admin and contact Payfast Support.
                break;
            case 'PENDING': // We've got problems, notify admin and contact Payfast Support.
                break;
            default: // We've got problems, notify admin to check logs.
                break;
        }
    }       
        
}
```    

