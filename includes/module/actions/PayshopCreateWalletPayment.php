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
class PayshopCreateWalletPayment
{
    /**
     * @var Modulo
     */
    private $module;

    /**
     * @var PayshopClient
     */
    private $payshopSDK;

    /**
     * @var PayshopCreatePaymentOrder
     */
    private $payshopCreatePaymentOrder;

    /**
     * @var PayshopUpdateOrder
     */
    private $payshopUpdateOrder;

    /**
     * Class constructor
     *
     * @param Module $module
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->payshopSDK = PayshopClientFactory::getInstance();
        $this->payshopCreatePaymentOrder = new PayshopCreatePaymentOrder($module);
        $this->payshopUpdateOrder = new PayshopUpdateOrder($module);
    }

    /**
     * Process webhook request sent by the gateway
     *
     * @param array $data
     *
     * @return bool
     */
    public function execute($data)
    {
        $transation = PayshopHelpers::getTransacion('order_id', $data['order_id']);
        $paymentOrderUUID = $transation['charge_id'];

        $paymentResponse = $this->sendWalletPayment($paymentOrderUUID, $data);

        if (isset($paymentResponse['response']['order'])) {
            if ($this->isPaymentSuccessful($paymentResponse['response']['order'])) {
                return $this->markOrderAsPaid($paymentOrderUUID, $data);
            }
        }

        if (isset($paymentResponse['response']['details'])) {
            $key = '_payshop_googlepay_paid_' . $data['order_id'];
            $_SESSION[$key] = true;

            return $paymentResponse['response']['details'];
        }

        return $this->markOrderAsRefused($paymentOrderUUID, $data);
    }

    /**
     * Make payment wallet request
     *
     * @param array $paymentOrder
     * @param array $data
     *
     * @return array
     */
    private function sendWalletPayment($paymentOrderUUID, $data)
    {
        return $this->payshopSDK->paymentWallet([
            'order_uuid' => $paymentOrderUUID,
            'wallet' => $data['payment_type'],
            'payload' => $data['payload'],
            'customer_ip' => $_SERVER['REMOTE_ADDR'],
            'flow' => 'APP',
        ]);
    }

    /**
     * Check if the payment is successful
     *
     * @param array $paymentOrder
     *
     * @return bool
     */
    private function isPaymentSuccessful($paymentOrder)
    {
        return $paymentOrder['status'] == 'SUCCESS' && $paymentOrder['paid'] == true;
    }

    /**
     * Mark order as paid
     *
     * @param array $paymentOrder
     * @param array $data
     *
     * @return bool
     */
    private function markOrderAsPaid($paymentOrderUUID, $data)
    {
        $this->payshopUpdateOrder->execute(
            $data['payment_type'],
            $data['order_id'],
            'PAYSHOP_ORDER_STATUS_PAID',
            $paymentOrderUUID
        );

        return true;
    }

    /**
     * Mark order as refused
     *
     * @return bool
     */
    private function markOrderAsRefused($paymentOrderUUID, $data)
    {
        $this->payshopUpdateOrder->execute(
            $data['payment_type'],
            $data['order_id'],
            'PAYSHOP_ORDER_STATUS_PAYMENT_ERROR',
            $paymentOrderUUID
        );

        return false;
    }
}
