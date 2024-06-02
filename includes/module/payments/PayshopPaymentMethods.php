<?php

class PayshopPaymentMethods
{
    const CREDIT_CARD = 'card';
    const MB_WAY ='mbway';
    const PAYSHOP_REFERENCE = 'payshop_reference';
    const MULTIBANCO ='multibanco';

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

        if (Configuration::get('PAYSHOP_CREDIT_CARD') && Configuration::get('PAYSHOP_CARD_SERVICE_UUID') != '') {
            $paymentOptions[] =  $this->creditCard->register();
        }

        if (Configuration::get('PAYSHOP_MBWAY')  && Configuration::get('PAYSHOP_MBWAY_SERVICE_UUID') != '') {
            $paymentOptions[] =  $this->mbWay->register();
        }

        if (Configuration::get('PAYSHOP_PAYSHOP_REFERENCE')  && Configuration::get('PAYSHOP_REFERENCE_SERVICE_UUID') != '') {
            $paymentOptions[] =  $this->payshopReference->register();
        }

        if (Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE')  && Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE_SERVICE_UUID') != '') {
            $paymentOptions[] =  $this->payshopMultibanco->register();
        }

        return $paymentOptions;
    }


}