<?php

class PayshopPaypal
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Register the Paypal reference payment method
     * 
     * @return PaymentOption
     */
    public function register()
    {
        $formAction = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessPaypal'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
            'moduleUrl' => $this->module->path
        ])
          ->fetch('module:payshop/views/templates/hook/payments/paypal.tpl');

        $payshopPaypalCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopPaypalCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with Paypal', 'PayshopPaypal'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/paypal.png');

        return $payshopPaypalCheckout;
    }
}