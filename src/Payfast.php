<?php

namespace Billow;

use Billow\Contracts\Payment;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

class Payfast implements Payment
{
    protected $merchant;

    protected $buyer;

    protected $merchantReference;

    protected $amount;

    protected $item;

    protected $output;

    protected $vars;


    public function __construct()
    {
        $this->merchant = config('payfast.merchant');
    }

    public function getMerchant()
    {
        return $this->merchant;
    }

    public function setBuyer($first, $last, $email)
    {
        $this->buyer = [
            'name_first'    => $first,
            'name_last'     => $last,
            'email_address' => $email
        ];
    }

    public function setMerchantReference($reference)
    {
        $this->merchantReference = $reference;
    }

    public function setItem($item, $description)
    {
        $this->item = [
            'item_name'         => $item,
            'item_description'  => $description,
        ];
    }

    public function setAmount($amount)
    {
        $this->amount = Money::fromString((string) $amount, new Currency(config('payfast.currency')));
    }

    public function paymentForm()
    {
        $this->vars = $this->paymentVars();

        $this->buildQueryString();

        return $this->buildForm();
    }

    public function paymentVars()
    {
        return array_merge($this->merchant, $this->buyer, ['m_payment_id' => $this->merchantReference, 'amount' => $this->amount], $this->item);
    }

    public function buildQueryString()
    {

        foreach($this->vars as $key => $val )
        {
            if(!empty($val))
            {
                $this->output .= $key .'='. urlencode( trim( $val ) ) .'&';
            }
        }

        $this->output = substr( $this->output, 0, -1 );

        if( isset( $passPhrase ) )
        {
            $this->output .= '&passphrase='.$passPhrase;
        }

        $vars['signature'] = md5( $this->output );
    }

    public function buildForm()
    {
        $pfHost = config('payfast.testing') ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';

        $htmlForm = '<form id="payfast-pay-form" action="https://'.$pfHost.'/eng/process" method="post">';

        foreach($this->vars as $name => $value)
        {
            $htmlForm .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
        }

        //$htmlForm .= '<button type="submit">Pay Now</button>';

        return $htmlForm.'</form>';
    }

}