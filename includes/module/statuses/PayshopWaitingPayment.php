<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class PayshopWaitingPayment
{
    /**
     * Register Waiting payment order status
     *
     * @return void
     */
    public static function register()
    {
        $orderStatusRegistered = Configuration::get('PAYSHOP_ORDER_STATUS_WAITING_PAYMENT');

        if ($orderStatusRegistered) {
            return;
        }

        $order_state = new OrderState();

        $order_state->name = [];
        foreach (Language::getLanguages() as $language) {
            if (Tools::strtolower($language['iso_code']) == 'pt') {
                $order_state->name[$language['id_lang']] = 'Aguardando pagamento';
            } else {
                $order_state->name[$language['id_lang']] = 'Waiting payment';
            }
        }

        $order_state->send_email = false;
        $order_state->color = '#fffb96';
        $order_state->hidden = false;
        $order_state->delivery = false;
        $order_state->logable = false;
        $order_state->invoice = false;
        $order_state->module_name = 'payshop';
        $order_state->paid = false;

        // $order_state->template = 'pending';

        if ($order_state->add()) {
            $source = _PS_MODULE_DIR_ . 'payshop/logo.png';
            $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
            copy($source, $destination);

            Configuration::updateValue('PAYSHOP_ORDER_STATUS_WAITING_PAYMENT', (int) $order_state->id);
        }
    }
}
