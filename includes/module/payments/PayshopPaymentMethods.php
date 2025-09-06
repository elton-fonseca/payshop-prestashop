<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class PayshopPaymentMethods
{
    const CREDIT_CARD = 'card';
    const MB_WAY = 'mbway';
    const PAYSHOP_REFERENCE = 'payshop_reference';
    const MULTIBANCO = 'multibanco';
    const GOOGLEPAY = 'googlepay';
    const APPLEPAY = 'applepay';
    const PAYPAL = 'paypal';
    const CLICKTOPAY = 'clicktopay';

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

    /**
     * @var PayshopGooglepay
     */
    private $payshopGooglepay;

    /**
     * @var PayshopApplepay
     */
    private $payshopApplepay;

    /**
     * @var PayshopPaypal
     */
    private $payshopPaypal;

    /**
     * @var PayshopClicktopay
     */
    private $payshopClicktopay;

    public function __construct($module)
    {
        $this->creditCard = new PayshopCreditCard($module);
        $this->mbWay = new PayshopMBWay($module);
        $this->payshopReference = new PayshopReference($module);
        $this->payshopMultibanco = new PayshopMultibanco($module);
        $this->payshopGooglepay = new PayshopGooglepay($module);
        $this->payshopApplepay = new PayshopApplepay($module);
        $this->payshopPaypal = new PayshopPaypal($module);
        $this->payshopClicktopay = new PayshopClicktopay($module);
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
            $paymentOptions[] = $this->creditCard->register();
        }

        if (Configuration::get('PAYSHOP_MBWAY') && Configuration::get('PAYSHOP_MBWAY_SERVICE_UUID') != '') {
            $paymentOptions[] = $this->mbWay->register();
        }

        if (Configuration::get('PAYSHOP_PAYSHOP_REFERENCE') && Configuration::get('PAYSHOP_REFERENCE_SERVICE_UUID') != '') {
            $paymentOptions[] = $this->payshopReference->register();
        }

        if (Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE') && Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE_SERVICE_UUID') != '') {
            $paymentOptions[] = $this->payshopMultibanco->register();
        }

        if (Configuration::get('PAYSHOP_GOOGLEPAY') && Configuration::get('PAYSHOP_GOOGLEPAY_SERVICE_UUID') != '') {
            $paymentOptions[] = $this->payshopGooglepay->register();
        }

        if (Configuration::get('PAYSHOP_APPLEPAY') && Configuration::get('PAYSHOP_APPLEPAY_SERVICE_UUID') != '') {
            $paymentOptions[] = $this->payshopApplepay->register();
        }

        if (Configuration::get('PAYSHOP_PAYPAL') && Configuration::get('PAYSHOP_PAYPAL_SERVICE_UUID') != '') {
            $paymentOptions[] = $this->payshopPaypal->register();
        }

        if (Configuration::get('PAYSHOP_CLICKTOPAY') && Configuration::get('PAYSHOP_CLICKTOPAY_SERVICE_UUID') != '') {
            $paymentOptions[] = $this->payshopApplepay->register();
        }

        return $paymentOptions;
    }
}
