<?php

class PayshopDynamicForm
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
            'shopUrl' => $this->module->context->link->getBaseLink(),
            'moduleUrl' => $this->module->path,

            'cartId' => $this->module->context->cart->id,

            'isProduction' => PayshopClientFactory::isProduction() ? 'true' : 'false',
            'publicKey' => PayshopClientFactory::getPublicKey(),
            'enablePaymentMethods' => $this->enablePaymentMethods(),

            'mbwayView' => $this->module->pathDir . '/views/templates/hook/payments/_mbway.tpl',
            'referencesView' => $this->module->pathDir . '/views/templates/hook/payments/_references.tpl',
            'loadingView' => $this->module->pathDir . '/views/templates/hook/payments/_loading.tpl'
        ])
          ->fetch('module:payshop/views/templates/hook/payments/dynamic-form.tpl');

        $payshopCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopCheckout->setForm($paymentForm)
            ->setCallToActionText(
                $this->module->l('Pay with: ') . $this->paymentsDescription()
            )
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/payshop-icon.png');

        return $payshopCheckout;
    }

    /**
     * Get enabled payment methods
     * 
     * @return string 
     */
    private function enablePaymentMethods()
    {
        $jsArray = '[';

        $jsArray .= Configuration::get('PAYSHOP_CREDIT_CARD') ? '"card",' : '';
        $jsArray .= Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE') ? '"multibanco",' : '';
        $jsArray .= Configuration::get('PAYSHOP_PAYSHOP_REFERENCE') ? '"payshop_reference",' : '';
        $jsArray .= Configuration::get('PAYSHOP_CREDIT_MBWAY') ? '"mbway",' : '';

        $jsArray .= ']';

        return $jsArray;
    }

    /**
     * Get payment description
     * 
     * @return string 
     */
    public function paymentsDescription()
    {
        $payments = [
            'PAYSHOP_CREDIT_CARD' => $this->module->l('Credit/Debit Card'),
            'PAYSHOP_MULTIBANCO_REFERENCE' => $this->module->l('Multibanco'),
            'PAYSHOP_PAYSHOP_REFERENCE' => $this->module->l('Payshop Reference'),
            'PAYSHOP_CREDIT_MBWAY' => $this->module->l('MBWay')
        ];

        $paymentsDescription = [];
        foreach ($payments as $configKey => $description) {
            if (!Configuration::get($configKey)) {
                continue;
            }

            $paymentsDescription[] = $description;
        }

        return implode(', ', $paymentsDescription);
    }
}