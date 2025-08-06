<?php

/**
 * 2007-2022 PrestaShop
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
 * @copyright 2007-2022 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

 class PayshopUpdateOrder
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
     * Update order and payshop transaction with new status
     *
     * @param string $paymentMethod
     * @param int $prestashopOrderId
     * @param string $newOrderStatus
     * @param int $paymentOrderId
     * @return void
     */
    public function execute(
        $paymentMethod,
        $prestashopOrderId,
        $newOrderStatus,
        $paymentOrderId
    )
    {
        $this->addPrestashopOrderPayment(
            $paymentMethod,
            $prestashopOrderId, 
            $newOrderStatus,
            $paymentOrderId
        );

        $this->updatePayshopTransaction(
            $prestashopOrderId,
            $newOrderStatus,
            $paymentOrderId
        );

        $this->updatePrestashopOrder($prestashopOrderId, $newOrderStatus);
    }

    /**
     * Add Prestashop order payment and invoice
     *
     * @param string $paymentMethod
     * @param int $prestashopOrderId
     * @param string $newOrderStatus
     * @param int $payshopChargeId
     * @return int
     * @throws Exception
     */
    private function addPrestashopOrderPayment(
        $paymentMethod,
        $prestashopOrderId, 
        $newOrderStatus,
        $paymentOrderId
    )
    {
        if ('PAYSHOP_ORDER_STATUS_PAID' != $newOrderStatus) {
            return;
        }

        $transaction = PayshopHelpers::getTransacion('order_id', $prestashopOrderId);

        $baseOrder = new Order($prestashopOrderId);

        $id_currency_eur = Currency::getIdByIsoCode('EUR');
        $currency = new Currency($id_currency_eur);

        $amount = (float) $transaction['total'];
        $paymentAdicioned = $baseOrder->addOrderPayment($amount, $paymentMethod, $paymentOrderId, $currency);

        if (!$paymentAdicioned) {
            throw new Exception(
                PayshopHelpers::errorMessageProcessTransation(
                    $this->module,
                    $prestashopOrderId,
                    $paymentOrderId
                )
            );
        }

        $baseOrder->setInvoice(true);
    }


    /**
     * Update Payshop transaction status
     * 
     * @param int $prestashopOrderId
     * @param string $newOrderStatus
     * @param int $paymentOrderId
     * @return bool
     * @throws Exception
     */
     private function updatePayshopTransaction(
        $prestashopOrderId,
        $newOrderStatus,
        $paymentOrderId
    )
    {
        $transaction = new PayshopTransaction();
        $transaction->where('order_id', '=', $prestashopOrderId);

        $isUpdated = $transaction->update([
            'payment_status' => $newOrderStatus,
            'charge_id' => $paymentOrderId,
            'instrument_id' => '',
            'payment_id' => ''
        ]);

        if (!$isUpdated) {
            throw new Exception(
                PayshopHelpers::errorMessageProcessTransation(
                    $this->module,
                    $prestashopOrderId,
                    $paymentOrderId
                )
            );
        }

        return $isUpdated;
    }

    /**
     * Update Prestashop order status
     *
     * @param int $prestashopOrderId
     * @param string $newOrderStatus
     * @return int
     * @throws Exception
     */
    private function updatePrestashopOrder($prestashopOrderId, $newOrderStatus)
    {
        if ('PAYSHOP_ORDER_STATUS_WAITING_PAYMENT' == $newOrderStatus) {
            return;
        }

        $newOrderStatusID = Configuration::get($newOrderStatus);

        $history = new OrderHistory();
        $history->id_order = (int) $prestashopOrderId;
        $history->changeIdOrderState($newOrderStatusID, $prestashopOrderId);

        $history->addWithemail();
    }
 }