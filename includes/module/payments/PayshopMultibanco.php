<?php

class PayshopMultibanco
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function register()
    {
        $formAction = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessMultibanco'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
            'moduleUrl' => $this->module->path
        ])
          ->fetch('module:payshop/views/templates/hook/payments/multibanco.tpl');

        $payshopMultibancoCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopMultibancoCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with Multibanco Reference', 'PayshopMultibanco'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/multibanco.png');

        return $payshopMultibancoCheckout;
    }
}