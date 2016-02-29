<?php

namespace Billow;

use Billow\Contracts\Payment;
use Illuminate\Http\Request;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;
use Illuminate\Support\Facades\Log;

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
        $this->amount = Money::fromString((string) $amount, new Currency(config('payfast.currency')))->getConvertedAmount();
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

    public function completePayment(Request $request)
    {
        $this->setHeader();

        $pfHost = config('payfast.testing') ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';

        // Posted variables from ITN
        $pfData = $request->all();

        // Strip any slashes in data
        foreach( $pfData as $key => $val )
        {
            $pfData[$key] = stripslashes( $val );
        }

        $pfParamString = '';

        // $pfData includes of ALL the fields posted through from PayFast, this includes the empty strings
        foreach( $pfData as $key => $val )
        {
            if( $key != 'signature' )
            {
                $pfParamString .= $key .'='. urlencode( $val ) .'&';
            }
        }

        // Remove the last '&' from the parameter string
        $pfParamString = substr( $pfParamString, 0, -1 );
        $pfTempParamString = $pfParamString;
        // If a passphrase has been set in the PayFast Settings, then it needs to be included in the signature string.
        $passPhrase = ''; //You need to get this from a constant or stored in you website database
        /// !!!!!!!!!!!!!! If you testing your integration in the sandbox, the passPhrase needs to be empty !!!!!!!!!!!!
        if( !empty( $passPhrase ) && !config('payfast.testing') )
        {
            $pfTempParamString .= '&passphrase='.urlencode( $passPhrase );
        }
        $signature = md5( $pfTempParamString );

        if($signature!=$pfData['signature'])
        {
            Log::info('invalid signature');
            die('Invalid Signature');
        }

        // Variable initialization
        $validHosts = array(
            'www.payfast.co.za',
            'sandbox.payfast.co.za',
            'w1w.payfast.co.za',
            'w2w.payfast.co.za',
        );

        $validIps = array();

        foreach( $validHosts as $pfHostname )
        {
            $ips = gethostbynamel( $pfHostname );

            if( $ips !== false )
            {
                $validIps = array_merge( $validIps, $ips );
            }
        }

        // Remove duplicates
        $validIps = array_unique( $validIps );

        if( !in_array( $_SERVER['REMOTE_ADDR'], $validIps ) )
        {
            die('Source IP not Valid');
        }

        $cartTotal = 200.22; //This amount needs to be sourced from your application
        if( abs( floatval( $cartTotal ) - floatval( $pfData['amount_gross'] ) ) > 0.01 )
        {
            Log::info('amounts miss match');
            die('Amounts Mismatch');
        }

        switch( $pfData['payment_status'] )
        {
            case 'COMPLETE':
                Log::info('complete');
                break;
            case 'FAILED':
                Log::info('failed');
                break;
            case 'PENDING':
                Log::info('pending');
                break;
            default:
                Log::info('unknown');
                break;
        }
    }

    public function setHeader()
    {
        header( 'HTTP/1.0 200 OK' );
        flush();
    }

}