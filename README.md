# Laravel Payfast

A dead simple Laravel payment processing class for payments through payfast.co.za. This package only supports ITN transactions. Laravel Payfast is strictly use at own risk.

## Installation

Add Laravel Payfast to your composer.json

```bash
composer require billowapp/payfast
```

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
        'merchant_id' => env('PF_MERCHANT_ID', '10000100'), // TEST Credentials. Replace with your merchant ID from Payfast.
        'merchant_key' => env('PF_MERCHANT_KEY', '46f0cd694581a'), // TEST Credentials. Replace with your merchant key from Payfast.
        'return_url' => env('PF_RETURN_URL', 'http://your-domain.co.za/success'), // Redirect URL on Success.
        'cancel_url' => env('PF_CANCEL_URL', 'http://your-domain.co.za/cancel'), // Redirect URL on Cancellation.
        'notify_url' => env('PF_ITN_URL', 'http://your-domain.co.za/itn'), // ITN URL.
    ],

];

```
### Usage

Creating a payment returns an html form ready to POST to payfast. When the customer submits the form they will be redirected to payfast to complete payment. Upon successful payment the customer will be returned to the specified 'return_url' and in the case of a cancellation they will be returned to the specified 'cancel_url'

---
**NOTE**

If you want to use subscripions, make sure to set your merchant's passphrase in the config file for this package. It is required for subscriptions.

---

```php

use Billow\Contracts\PaymentProcessor;

Class PaymentController extends Controller
{

    public function confirmPayment(PaymentProcessor $payfast)
    {
        // Eloqunet example.
        $cartTotal = 9999;
        $order = Order::create([
            'm_payment_id' => '001', // A unique reference for the order.
            'amount'       => $cartTotal
        ]);

        // Build up payment Paramaters.
        $payfast->setBuyer('first name', 'last name', 'email');
        $payfast->setAmount($order->amount);
        $payfast->setItem('item-title', 'item-description');
        $payfast->setMerchantReference($order->m_payment_id);

        // Optionally send confirmation email to seller
        $payfast->setEmailConfirmation();
        $payfast->setConfirmationAddress(env('PAYFAST_CONFIRMATION_EMAIL'));

        // Optionally make this a subscription
        $payfast->setSubscriptionType();    // will default to 1
        $payfast->setFrequency();           // will default to 3 = monthly if not set
        $payfast->setCycles();              // will default to 0 = indefinite if not set

        // Return the payment form.
        return $payfast->paymentForm('Place Order');
    }

}
```

### ITN Responses

Payfast will send a POST request to notify the merchant (You) with a status on the transaction. This will allow you to update your order status based on the appropriate status sent back from Payfast. You are not forced to use the key 'm_payment_id' to store your merchant reference but this is the the key that will be returned back to you from Payfast for further verification.

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

The response variables POSTED back by payfast may be accessed as follows:

```php

 return $payfast->responseVars();

```

Variables Returned by Payfast

```php

[
    'm_payment_id' => '',
    'pf_payment_id' => '',
    'payment_status' => '',
    'item_name' => '',
    'item_description' => '',
    'amount_gross' => '',
    'amount_fee' => '',
    'amount_net' => '',
    'custom_str1' => '',
    'custom_str2' => '',
    'custom_str3' => '',
    'custom_str4' => '',
    'custom_str5' => '',
    'custom_int1' => '',
    'custom_int2' => '',
    'custom_int3' => '',
    'custom_int4' => '',
    'custom_int5' => '',
    'name_first' => '',
    'name_last' => '',
    'email_address' => '',
    'merchant_id' => '',
    'signature' => '',
];

```

### Amounts

The cart total may be set in 2 ways, as a string value:

```php

    $cartTotal = '99.99';

    $payfast->setAmount($cartTotal);
```

Or as an Integer. In the case of an integer, the cart total must be passed through in cents, as follows:

```php

$cartTotal = 9999; // Laravel Payfast will parse this value and format it accordingly. See sebastianbergmann/money
$payfast->setAmount($cartTotal);

```

### Payment Form

By default the paymentForm() method will return a compiled HTML form including a submit button. There are 3 configurations available for the submit button.

```php

$payfast->paymentForm() // Default Text: 'Pay Now'

$payfast->paymentForm(false) // No submit button, handy for submitting the form via javascript

$payfast->paymentForm('Confirm and Pay') // Override Default Submit Button Text.

```
