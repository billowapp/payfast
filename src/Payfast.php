<?php

namespace Billow;

use Billow\Contracts\PaymentProcessor;
use Billow\Utilities\Money;
use Exception;
use Illuminate\Http\Request;

class Payfast implements PaymentProcessor
{

    protected $merchant;

    protected $buyer;

    protected $merchantReference;

    protected $amount;

    protected $item;

    protected $output;

    protected $vars;

    protected $response_vars;

    protected $host;

    protected $button;

    protected $status;

    protected $custom_str1;

    protected $custom_str2;

    protected $custom_str3;

    protected $custom_str4;

    protected $custom_str5;

    protected $custom_int1;

    protected $custom_int2;
    
    protected $custom_int3;

    protected $custom_int4;

    protected $custom_int5;
    
    protected $payment_method;

    protected $passphrase;
    
    public function __construct()
    {
        $this->merchant = config('payfast.merchant');
    }
    
    public function setPassphrase($passphrase=null)
    {
        $this->passphrase = $passphrase;    
    }
    
    public function getPassphrase()
    {
        return $this->passphrase;
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
        $money = $this->newMoney($amount);
        $this->amount = $money->convertedAmount();
    }

    public function paymentForm($submitButton = true)
    {
        $this->button = $submitButton;
        $this->vars = $this->paymentVars();
        $this->buildQueryString();
        $this->vars['signature'] = md5($this->output);
        return $this->buildForm();
    }

    public function paymentVars()
    {
        return array_merge($this->merchant, $this->buyer, [
            'm_payment_id' => $this->merchantReference, 
            'amount' => $this->amount,
            'item_name'         => $this->item['item_name'],
            'item_description'  => $this->item['item_description'],
            'custom_int1' => $this->custom_int1,
            'custom_int2' => $this->custom_int2,
            'custom_int3' => $this->custom_int3,
            'custom_int4' => $this->custom_int4,
            'custom_int5' => $this->custom_int5,
            'custom_str1' => $this->custom_str1,
            'custom_str2' => $this->custom_str2,
            'custom_str3' => $this->custom_str3,
            'custom_str4' => $this->custom_str4,
            'custom_str5' => $this->custom_str5,
            'payment_method' => $this->payment_method
        ]);
    }

    public function buildQueryString()
    {
        foreach($this->vars as $key => $val )
        {
            if(!empty($val)) {
                $this->output .= $key .'='. urlencode( trim( $val ) ) .'&';
            }
        }
        $this->output = substr( $this->output, 0, -1 );
        
        /**
        * Set the new passphrase
        */
        $this->output .= '&passphrase='.$this->getPassphrase();
    }

    public function buildForm()
    {
        $this->getHost();
        $htmlForm = '<form id="payfast-pay-form" action="https://'.$this->host.'/eng/process" method="post">';
        foreach($this->vars as $name => $value)
        {
            $htmlForm .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
        }
        if($this->button)
        {
            $htmlForm .= '<button type="submit">'.$this->getSubmitButton().'</button>';
        }
        return $htmlForm.'</form>';
    }

    public function verify($request, $amount)
    {
        $this->setHeader();
        $this->response_vars = $request->all();
        $this->setAmount($amount);
        foreach($this->response_vars as $key => $val)
        {
            $this->vars[$key] = stripslashes($val);
        }
        $this->buildQueryString();
        $this->validSignature($request->get('signature'));
        $this->validateHost($request);
        $this->validateAmount($request->get('amount_gross'));
        $this->status = $request->get('payment_status');
        return $this;
    }

    public function status()
    {
        return $this->status;
    }

    public function setHeader()
    {
        header('HTTP/1.0 200 OK');
        flush();
    }

    public function validSignature($signature)
    {
        if($this->vars['signature'] === $signature)
        {
            return true;
        }else {
            throw new Exception('Invalid Signature');
        }
    }

    public function validateHost($request)
    {
        $hosts = $this->getHosts();

        if( !in_array( $request->server('REMOTE_ADDR'), $hosts ) )
        {
            throw new Exception('Not a valid Host');
        }

        return true;
    }

    public function getHosts()
    {
        $hosts = [];

        foreach(config('payfast.hosts') as $host) {
            $ips = gethostbynamel($host);
            if(count($ips) > 0) {
                foreach($ips as $ip) {
                    $hosts[] = $ip;
                }
            }
        }
        return array_unique($hosts);
    }

    public function validateAmount($grossAmount)
    {
        if($this->amount === $this->newMoney($grossAmount)->convertedAmount()) {
            return true;
        }else {
            throw new Exception('The gross amount does not match the order amount');
        }
    }

    public function newMoney($amount)
    {
        return(is_string($amount) || is_float($amount))
            ? (new Money)->fromString((string)$amount)
            : new Money($amount);
    }

    public function getHost()
    {
        return $this->host = config('payfast.testing') ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
    }

    public function getSubmitButton()
    {
        if(is_string($this->button)) {
            return $this->button;
        }

        if($this->button == true) {
            return 'Pay Now';
        }
        return false;
    }

    public function responseVars()
    {
        return $this->response_vars;
    }

    public function setCancelUrl($url)
    {
        $this->merchant['cancel_url'] = $url;
    }

    public function setReturnUrl($url)
    {
        $this->merchant['return_url'] = $url;
    }

    public function setNotifyUrl($url)
    {
        $this->merchant['notify_url'] = $url;
    }

    public function setCustomStr1($string = '')
    {
        $this->custom_str1 = $string;
    }

    public function setCustomStr2($string = '')
    {
        $this->custom_str2 = $string;
    }

    public function setCustomStr3($string = '')
    {
        $this->custom_str3 = $string;
    }

    public function setCustomStr4($string = '')
    {
        $this->custom_str4 = $string;
    }

    public function setCustomStr5($string = '')
    {
        $this->custom_str5 = $string;
    }

    public function setCustomInt1($int)
    {
        $this->custom_int1 = $int;
    }

    public function setCustomInt2($int)
    {
        $this->custom_int2 = $int;
    }

    public function setCustomInt3($int)
    {
        $this->custom_int3 = $int;
    }

    public function setCustomInt4($int)
    {
        $this->custom_int4 = $int;
    }

    public function setCustomInt5($int)
    {
        $this->custom_int5 = $int;
    }

    public function setPaymentMethod($method)
    {
        $this->payment_method = $method;
    }
}
