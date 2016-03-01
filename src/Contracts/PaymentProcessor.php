<?php

namespace Billow\Contracts;


interface PaymentProcessor
{

    /**
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @return void
     */
    public function setBuyer($first_name, $last_name, $email);

    /**
     * @param integer $amount
     * @return void
     */
    public function setAmount($amount);

    /**
     * @param string $item_title
     * @param string $item_description
     * @return void
     */
    public function setItem($item_title, $item_description);

    /**
     * @param string $merchant_reference
     * @return void
     */
    public function setMerchantReference($merchant_reference);

    /**
     * @param $response
     * @param integer $amount
     * @return self
     */
    public function verify($response, $amount);

    /**
     * @return string
     */
    public function status();

    /**
     * @return array
     */
    public function responseVars();

    /**
     * @param bool|string $submitButton
     * @return mixed
     */
    public function paymentForm($submitButton = true);

    /**
     * @return void
     */
    public function setHeader();

    /**
     * @param $signature
     * @return boolean
     */
    public function validSignature($signature);

    /**
     * @param $request
     * @return boolean
     */
    public function validateHost($request);
}