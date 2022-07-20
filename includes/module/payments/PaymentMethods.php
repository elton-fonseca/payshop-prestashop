<?php

class PaymentMethods
{
    private $module;

    private $creditCard;

    private $mbWay;

    private $payshopReference;


    public function __construct($module)
    {
        $this->module = $module;
        $this->creditCard = new CreditCard($module);
        $this->mbWay = new MBWay($module);
        $this->payshopReference = new PayshopReference($module);
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

        if (Configuration::get('PAYSHOP_PAYSHOP_REFERENCE') == true) {
            $paymentOptions[] =  $this->payshopReference->register();
        }

        return $paymentOptions;
    }
}