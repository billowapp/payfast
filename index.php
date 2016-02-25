<?php

use Billow\Payfast;

require __DIR__ . '/vendor/autoload.php';

$payfast = new Payfast();

$payfast->setBuyer('warren', 'hansen', 'warren@billow.co.za');

$payfast->setMerchantReference(uniqid());

$payfast->setItem('urn', 'Urn Descriptor');

$payfast->setAmount(200.22);

dd($payfast->pay());