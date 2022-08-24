<?php

class PayshopPaymentMethods
{
    /**
     * @var PayshopCreditCard
     */
    private $creditCard;

    /**
     * @var PayshopMBWay
     */
    private $mbWay;

    /**
     * @var PayshopReference
     */
    private $payshopReference;

    /**
     * @var PayshopMultibanco
     */
    private $payshopMultibanco;

    public function __construct($module)
    {
        $this->module = $module;
        $this->creditCard = new PayshopCreditCard($module);
        $this->mbWay = new PayshopMBWay($module);
        $this->payshopReference = new PayshopReference($module);
        $this->payshopMultibanco = new PayshopMultibanco($module);
    }

    /**
     * Register payment methods
     * 
     * @return array
     */
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

        if (Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE') == true) {
            $paymentOptions[] =  $this->payshopMultibanco->register();
        }

        return $paymentOptions;
    }


}