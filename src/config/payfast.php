<?php

return [

    'production' => false,

    'credentials' => [
        'merchant_id' => '',
        'merchant_key' => '',
    ],

    'urls' => [
        'return_url' => 'http://dev.biotree.earth/order/successful',
        'cancel_url' => 'http://dev.biotree.earth/order/cancel',
        'notify_url' => 'http://dev.biotree.earth/order/itn',
    ]

];