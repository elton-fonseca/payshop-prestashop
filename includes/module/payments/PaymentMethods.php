<?php

class PaymentMethods
{
    private $module;

    private $creditCard;

    public function __construct($module)
    {
        $this->module = $module;
        $this->creditCard = new CreditCard($module);
    }

    public function getPaymentOptions($params)
    {
        $paymentOptions = [];

        if (Configuration::get('PAYSHOP_CREDIT_CARD') == true) {
            $paymentOptions[] =  $this->creditCard->register();
        }

        return $paymentOptions;
    }
}