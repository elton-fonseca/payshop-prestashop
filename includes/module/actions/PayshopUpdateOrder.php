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
class PayshopUpdateOrder
{
    /**
     * @var Module
     */
    private $module;

    /**
     * Class constructor
     *
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Update order and payshop transaction with new status
     *
     * @param string $paymentMethod
     * @param int $prestashopOrderId
     * @param string $newOrderStatus
     * @param string $paymentOrderId
     *
     * @return void
     */
    public function execute(
        $paymentMethod,
        $prestashopOrderId,
        $newOrderStatus,
        $paymentOrderId,
    ) {
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
     * @param string $paymentOrderId
     *
     * @return void
     *
     * @throws Exception
     */
    private function addPrestashopOrderPayment(
        $paymentMethod,
        $prestashopOrderId,
        $newOrderStatus,
        $paymentOrderId
    ): void {
        if ('PAYSHOP_ORDER_STATUS_PAID' != $newOrderStatus) {
            return;
        }

        $transaction = PayshopHelpers::getTransacion('order_id', $prestashopOrderId);

        $baseOrder = new Order($prestashopOrderId);

        $id_currency_eur = Currency::getIdByIsoCode('EUR');
        $currency = new Currency($id_currency_eur);

        $amount = (string) $transaction['total'];
        $paymentAdicioned = $baseOrder->addOrderPayment($amount, $paymentMethod, $paymentOrderId, $currency);

        if (!$paymentAdicioned) {
            throw new Exception(PayshopHelpers::errorMessageProcessTransation($this->module, $prestashopOrderId, $paymentOrderId));
        }

        $baseOrder->setInvoice(true);
    }

    /**
     * Update Payshop transaction status
     *
     * @param int $prestashopOrderId
     * @param string $newOrderStatus
     * @param string $paymentOrderId
     *
     * @return bool
     *
     * @throws Exception
     */
    private function updatePayshopTransaction(
        $prestashopOrderId,
        $newOrderStatus,
        $paymentOrderId,
    ) {
        $transaction = new PayshopTransaction();
        $transaction->where('order_id', '=', $prestashopOrderId);

        $isUpdated = $transaction->update([
            'payment_status' => $newOrderStatus,
            'charge_id' => $paymentOrderId,
            'instrument_id' => '',
            'payment_id' => '',
        ]);

        if (!$isUpdated) {
            throw new Exception(PayshopHelpers::errorMessageProcessTransation($this->module, $prestashopOrderId, $paymentOrderId));
        }

        return $isUpdated;
    }

    /**
     * Update Prestashop order status
     *
     * @param int $prestashopOrderId
     * @param string $newOrderStatus
     *
     * @return void
     *
     * @throws Exception
     */
    private function updatePrestashopOrder($prestashopOrderId, $newOrderStatus)
    {
        if ('PAYSHOP_ORDER_STATUS_WAITING_PAYMENT' == $newOrderStatus) {
            return;
        }

        $newOrderStatusID = (int) Configuration::get($newOrderStatus);

        $history = new OrderHistory();
        $history->id_order = (int) $prestashopOrderId;
        $history->changeIdOrderState($newOrderStatusID, $prestashopOrderId);

        $history->addWithemail();
    }
}
