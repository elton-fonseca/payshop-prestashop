<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class PayshopApplepay
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Register the ApplePay payment method
     *
     * @return PaymentOption
     */
    public function register()
    {
        $formAction = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessApplepay'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
            'moduleUrl' => $this->module->path,
        ])
          ->fetch('module:payshop/views/templates/hook/payments/applepay.tpl');

        $payshopApplepayCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopApplepayCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with Apple Pay', 'PayshopApplepay'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/applepay.png');

        return $payshopApplepayCheckout;
    }
}
