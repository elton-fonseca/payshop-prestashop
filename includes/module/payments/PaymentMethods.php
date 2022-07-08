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

        $paymentOptions[] =  $this->creditCard->register();

        return $paymentOptions;
    }
}