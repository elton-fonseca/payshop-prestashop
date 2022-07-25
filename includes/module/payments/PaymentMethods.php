<?php

class PaymentMethods
{
    private $module;

    private $payshopDynamicForm;

    public function __construct($module)
    {
        $this->module = $module;
        $this->payshopDynamicForm = new PayshopDynamicForm($module);
    }

    public function getPaymentOptions($params)
    {
        $paymentOptions = [];

        $paymentOptions[] =  $this->payshopDynamicForm->register();

        return $paymentOptions;
    }
}