<?php

class PayshopPaymentError
{
    /**
     * Register Payment Error order status
     *
     * @return void
     */
    public static function register()
    {
        $orderStatusRegistered = Configuration::get('PAYSHOP_ORDER_STATUS_PAYMENT_ERROR');

        if ($orderStatusRegistered) {
            return;
        }

        $order_state = new OrderState();

        $order_state->name = array();
        foreach (Language::getLanguages() as $language) {
            if (Tools::strtolower($language['iso_code']) == 'pt') {
                $order_state->name[$language['id_lang']] = 'Erro de pagamento';
            } else {
                $order_state->name[$language['id_lang']] = 'Payment Error';
            }
        }

        $order_state->send_email = true;
        $order_state->color = '#E74C3C';
        $order_state->hidden = false;
        $order_state->delivery = false;
        $order_state->logable = false;
        $order_state->invoice = false;
        $order_state->module_name = 'payshop';
        $order_state->paid = false;

        $order_state->template = 'payment_error';

        if ($order_state->add()) {
            $source = _PS_MODULE_DIR_ . 'payshop/logo.png';
            $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
            copy($source, $destination);

            Configuration::updateValue('PAYSHOP_ORDER_STATUS_PAYMENT_ERROR', (int) $order_state->id);
        }
    }
}