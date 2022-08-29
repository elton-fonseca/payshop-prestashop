<?php

class PayshopMBWay
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
            'ControlerName'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
            'moduleUrl' => $this->module->path,
            'shopUrl' => $this->module->context->shop->getBaseURL()
        ])
          ->fetch('module:payshop/views/templates/hook/payments/mbway.tpl');

        $mbWayCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $mbWayCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with MBWay', 'PayshopMBWay'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/mbway.png');

        return $mbWayCheckout;
    }
}