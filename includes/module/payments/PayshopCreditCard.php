<?php

class PayshopCreditCard
{
    /**
     * @var Payshop
     */
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Register the credit card payment method
     * 
     * @return PaymentOption
     */
    public function register()
    {
        $formAction = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessCard',
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction            
        ])
          ->fetch('module:payshop/views/templates/hook/payments/credit-card.tpl');

        $creditCardCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $creditCardCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with credit and debit cards', 'PayshopCreditCard'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/visa_mc.png');

        return $creditCardCheckout;
    }
}