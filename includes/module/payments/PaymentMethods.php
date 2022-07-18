<?php

class PaymentMethods
{
    private $module;

    private $creditCard;

    private $mbWay;

    public function __construct($module)
    {
        $this->module = $module;
        $this->creditCard = new CreditCard($module);
        $this->mbWay = new MBWay($module);
    }

    public function getPaymentOptions($params)
    {
        $paymentOptions = [];

        if (Configuration::get('PAYSHOP_CREDIT_CARD') == true) {
            $paymentOptions[] =  $this->creditCard->register();
        }

        if (Configuration::get('PAYSHOP_CREDIT_MBWAY') == true) {
            $paymentOptions[] =  $this->mbWay->register();
        }

        return $paymentOptions;
    }
}