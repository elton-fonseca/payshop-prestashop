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

 class UpdateOrder
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
     * @return int
     * @throws Exception
     */
    public function execute(
        $paymentMethod,
        $prestashopOrderId,
        $newOrderStatus,
        $payshopChargeId,
        $payshopInstrumentId,
        $payshopPaymentId = null
    )
    {
        $this->updatePrestashopOrder($prestashopOrderId, $newOrderStatus);

        $this->addPrestashopOrderPayment(
            $paymentMethod,
            $prestashopOrderId, 
            $newOrderStatus,
            $payshopChargeId
        );

        $this->updatePayshopTransaction(
            $prestashopOrderId,
            $newOrderStatus,
            $payshopChargeId,
            $payshopInstrumentId,
            $payshopPaymentId
        );
    }

    /**
     * Update Prestashop order status
     *
     * @param string $paymentMethod
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

    /**
     * Add Prestashop order payment and invoice
     *
     * @param string $paymentMethod
     * @return int
     * @throws Exception
     */
    private function addPrestashopOrderPayment(
        $paymentMethod,
        $prestashopOrderId, 
        $newOrderStatus,
        $payshopChargeId
    )
    {
        if ('PAYSHOP_ORDER_STATUS_PAID' != $newOrderStatus) {
            return;
        }

        $transaction = PayshopHelpers::getTransacion('order_id', $prestashopOrderId);

        $baseOrder = new Order($prestashopOrderId);

        $amount = (float) $transaction['total'];
        $baseOrder->addOrderPayment($amount, $paymentMethod, $payshopChargeId);
        $baseOrder->setInvoice(true);
    }


    /**
     * Update Payshop transaction status
     * 
     * @param string $prestashopOrderId
     * @param string $newOrderStatus
     * @param string $payshopChargeId
     * @param string $payshopInstrumentId
     * @param string $payshopPaymentId
     * @return bool
     * @throws Exception
     */
     private function updatePayshopTransaction(
        $prestashopOrderId,
        $newOrderStatus,
        $payshopChargeId,
        $payshopInstrumentId,
        $payshopPaymentId
    )
    {
        $transaction = new PayshopTransaction();
        $transaction->where('order_id', '=', $prestashopOrderId);

        $isUpdated = $transaction->update([
            'payment_status' => $newOrderStatus,
            'charge_id' => $payshopChargeId,
            'instrument_id' => $payshopInstrumentId,
            'payment_id' => $payshopPaymentId
        ]);

        if (!$isUpdated) {
            PayshopLog::generate(
                $this->module->l('Error while creating payshop transaction in database.')
            );

            throw new Exception($this->module->l('Error updating transaction'));
        }

        return $isUpdated;
    }
 }