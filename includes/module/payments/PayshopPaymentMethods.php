<?php

class PayshopPaymentMethods
{
    /**
     * @var PayshopDynamicForm
     */
    private $payshopDynamicForm;

    /**
     * Class constructor
     */
    public function __construct($module)
    {
        $this->payshopDynamicForm = new PayshopDynamicForm($module);
    }

    /**
     * Register payment methods
     * 
     * @return array
     */
    public function getPaymentOptions($params)
    {
        if ($this->notHasPaymentEnabled()) {
            return [];
        }

        $paymentOptions = [];

        $paymentOptions[] =  $this->payshopDynamicForm->register();

        return $paymentOptions;
    }

    /**
     * Check if has enabled payment methods
     * 
     * @return boolean
     */
    public function notHasPaymentEnabled()
    {
        return !Configuration::get('PAYSHOP_CREDIT_CARD') &&
            !Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE') &&
            !Configuration::get('PAYSHOP_PAYSHOP_REFERENCE') &&
            !Configuration::get('PAYSHOP_CREDIT_MBWAY');
    }
}