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

    public function getPaymentOptions($params)
    {
        $paymentOptions = [];

        $paymentOptions[] =  $this->payshopDynamicForm->register();

        return $paymentOptions;
    }
}