<?php


namespace Billow\Payfast\Test\Features;

use Billow\Payfast;
use Billow\Payfast\Test\TestCase;

class ValidateSignatureTest extends TestCase
{
    protected $vars;
    protected $output;

    /** @test */
    public function testShouldReturnValidSignature()
    {
        $payfast = new Payfast();
        $payfast->setMerchant([
            'merchant_id' => '10000100',
            'merchant_key' => '46f0cd694581a',
            'return_url' => 'http://your-domain.co.za/success',
            'cancel_url' => 'http://your-domain.co.za/cancel',
            'notify_url' => 'http://your-domain.co.za/itn',
        ]);
        $payfast->setBuyer('Jane', 'Doe', 'jane@example.com');
        $payfast->setAmount(100.50);
        $payfast->setItem('item-title', 'description');
        $payfast->setMerchantReference(1);

        /** generate signature on http://sandbox.payfast.co.za
         *  with POST CHECK tool
        */
        $sandboxGeneratedQueryString = 'merchant_id=10000100&merchant_key=46f0cd694581a&return_url=http%3A%2F%2Fyour-domain.co.za%2Fsuccess&cancel_url=http%3A%2F%2Fyour-domain.co.za%2Fcancel&notify_url=http%3A%2F%2Fyour-domain.co.za%2Fitn&name_first=Jane&name_last=Doe&email_address=jane%40example.com&m_payment_id=1&amount=100.50&item_name=item-title&item_description=description';
        $sandboxGeneratedSignature = '279d5d8fd4164b1f2fc17467afe4602b';

        $packageQueryString = $this->buildQueryString($payfast->paymentVars());
        $packageSignature = md5($packageQueryString);

        $this->assertSame($sandboxGeneratedQueryString, $packageQueryString);
        $this->assertSame($sandboxGeneratedSignature, $packageSignature);
    }

    /**
     * build query string
     *
     * @param array $vars
     * @return string
     */
    public function buildQueryString($vars, $passphrase = '')
    {
        $output = '';

        foreach($vars as $key => $val )
        {
            if(!empty($val)) {
                $output .= $key .'='. urlencode( trim( $val ) ) .'&';
            }
        }
        $output = substr( $output, 0, -1 );

        if( !empty( $passphrase ) )
        {
            $output .= '&passphrase=' . urlencode( trim( $passphrase ) );
        }

        return $output;
    }
}
