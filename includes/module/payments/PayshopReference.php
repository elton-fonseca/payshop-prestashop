<?php

class PayshopReference
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
            'PayshopReference'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
        ])
          ->fetch('module:payshop/views/templates/hook/payments/payshop-reference.tpl');

        $payshopReferenceCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopReferenceCheckout->setForm($paymentForm)
            ->setCallToActionText('Pay with Payshop Reference')
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/payshop-reference.png');

        return $payshopReferenceCheckout;
    }
}