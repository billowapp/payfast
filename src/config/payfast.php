<?php

return

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
]];