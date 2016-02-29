# Laravel5 Payfast

WARNING: Still In Development. This package is still in development and will no doubt cause issues should you install it at this point. We're ironing out the kinks and as soon as we're happy we'll give it a stable status. See ToDo's.

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

### ITN Responses

Payfast will send a POST request to notify the merchant (You) with a status on the transaction. This will allow you to update your order status based on the appropriate status.

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

### Amounts

The cart total may be set in 2 ways, as a string value:

```php

    $cartTotal = '99.99';

    $payfast->setAmount($cartTotal);
```

Or as an Integer. In the case of an integer, the cart total must be passed through in cents, as follows:

```php

$cartTotal = 9999; // Laravel5 Payfast will parse this value and format it accordingly. See sebastianbergmann/money
$payfast->setAmount($cartTotal);

```

### Payment Form

By default the getPaymentForm() method will return a compiled HTML form including a submit button. There are 3 configurations available for the submit button.

```php

$payfast->getPaymentForm() // Default Text: 'Pay Now'

$payfast->getPaymentForm(false) // No submit button, handy for submitting for via javascript

$payfast->getPaymentForm('Cofirm and Pay') // Override Default Submit Button Text.

```


