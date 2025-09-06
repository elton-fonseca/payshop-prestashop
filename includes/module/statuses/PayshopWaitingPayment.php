<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
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
