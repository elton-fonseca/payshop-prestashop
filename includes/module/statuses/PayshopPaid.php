<?php

class PayshopPaid
{
    /**
     * Register Paid order status
     *
     * @return void
     */
    public static function register()
    {
        $orderStatusRegistered = Configuration::get('PAYSHOP_ORDER_STATUS_PAID');

        if ($orderStatusRegistered) {
            return;
        }

        $order_state = new OrderState();

        $order_state->name = array();
        foreach (Language::getLanguages() as $language) {
            if (Tools::strtolower($language['iso_code']) == 'pt') {
                $order_state->name[$language['id_lang']] = 'pago';
            } else {
                $order_state->name[$language['id_lang']] = 'paid';
            }
        }

        $order_state->send_email = true;
        $order_state->color = '#ccfbff';
        $order_state->hidden = false;
        $order_state->delivery = false;
        $order_state->logable = false;
        $order_state->invoice = true;
        $order_state->module_name = 'payshop';
        $order_state->paid = true;

        $order_state->template = 'authorized';

        if ($order_state->add()) {
            $source = _PS_MODULE_DIR_ . 'payshop/logo.png';
            $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
            copy($source, $destination);

            Configuration::updateValue('PAYSHOP_ORDER_STATUS_PAID', (int) $order_state->id);
        }
    }
}