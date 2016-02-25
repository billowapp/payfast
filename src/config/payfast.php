<?php

return [

    'testing' => true, // Set to false when in production.

    'currency' => 'ZAR',

    'merchant' => [
        'merchant_id' => '10000100', // Replace with your merchant ID from Payfast.
        'merchant_key' => '46f0cd694581a', // Replace with your merchant key from Payfast.
        'return_url' => 'http://dev.biotree.earth/order/successful',
        'cancel_url' => 'http://dev.biotree.earth/order/cancel',
        'notify_url' => 'http://dev.biotree.earth/order/itn',
    ],

];