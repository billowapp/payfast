<?php

namespace Billow;

use Billow\Contracts\Payment;
use Illuminate\Support\Collection;

class Payfast implements Payment
{
    protected $merchant;

    protected $buyer;

    protected $merchantReference;

    protected $amount;

    protected $item;

    protected $formBuilder;

    protected $urls;

    protected $vars;

    protected $queryString;


    public function __construct()
    {
        $this->formBuilder = new FormBuilder();

        $this->merchant = config('payfast.credentials');
    }

    public function getMerchant()
    {
        return $this->merchant;
    }

    public function setBuyer($first, $last, $email)
    {
        $this->buyer = [
            'name_first' => $first,
            'name_last'  => $last,
            'email'      => $email
        ];
    }

    public function setMerchantReference($reference)
    {
        $this->merchantReference = $reference;
    }

    public function setItem($item, $description)
    {
        $this->item = [
            'item_name' => $item,
            'item_description' => $description,
        ];
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

}