<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PayshopReference
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
            'ProcessPayshop'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
            'moduleUrl' => $this->module->path
        ])
          ->fetch('module:payshop/views/templates/hook/payments/payshop-reference.tpl');

        $payshopReferenceCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopReferenceCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with Payshop Reference', 'PayshopReference'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/payshop-reference.png');

        return $payshopReferenceCheckout;
    }
}