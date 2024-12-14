<?php

class PayshopGooglepay
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Register the Multibanco reference payment method
     * 
     * @return PaymentOption
     */
    public function register()
    {
        $formAction = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessGooglepay'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
            'moduleUrl' => $this->module->path
        ])
          ->fetch('module:payshop/views/templates/hook/payments/googlepay.tpl');

        $payshopGooglepayCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopGooglepayCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with Google Pay', 'PayshopMultibanco'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/googlepay.png');

        return $payshopGooglepayCheckout;
    }
}