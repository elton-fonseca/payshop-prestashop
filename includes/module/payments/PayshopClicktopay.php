<?php

class PayshopClicktopay
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Register the ClickToPay payment method
     * 
     * @return PaymentOption
     */
    public function register()
    {
        $formAction = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessClicktopay'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
            'moduleUrl' => $this->module->path
        ])
          ->fetch('module:payshop/views/templates/hook/payments/clicktopay.tpl');

        $payshopClicktopayCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopClicktopayCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with ClickToPay', 'PayshopClicktopay'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/clicktopay.png');

        return $payshopClicktopayCheckout;
    }
}