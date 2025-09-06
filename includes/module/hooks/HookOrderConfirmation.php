<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */
class HookOrderConfirmation
{
    /**
     * @var Modulo
     */
    private $module;

    /**
     * Class constructor
     *
     * @param Module $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Display information on the order confirmation page
     *
     * @param Order $order
     *
     * @return string
     */
    public function execute($order)
    {
        $referencePaymentMethods = [
            'Payshop (Payshop Reference)',
            'Payshop (Multibanco)',
        ];

        if (in_array($order->payment, $referencePaymentMethods)) {
            $referencesDialog = new PayshopShowReferencesOrderConfirmation($this->module);

            return $referencesDialog->execute();
        }

        $transaction = PayshopHelpers::getTransacion('order_id', $order->id);

        if ($transaction['payment_status'] !== 'PAYSHOP_ORDER_STATUS_WAITING_PAYMENT') {
            return;
        }

        $googlepay = 'Payshop Online Payments (Google Pay)';

        if ($order->payment == $googlepay) {
            $googlepayDialog = new PayshopGooglepayOrderConfirmation($this->module);

            return $googlepayDialog->execute($order);
        }

        $applepay = 'Payshop Online Payments (Apple Pay)';

        if ($order->payment == $applepay) {
            $applepayDialog = new PayshopApplepayOrderConfirmation($this->module);

            return $applepayDialog->execute($order);
        }
    }
}
