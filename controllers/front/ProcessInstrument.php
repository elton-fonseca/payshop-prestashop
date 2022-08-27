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

class PayshopProcessInstrumentModuleFrontController extends ModuleFrontController
{
    /**
     * @var PayshopCreateOrder
     */
    private $payshopCreateOrder;

    /**
     * @var PayshopUpdateOrder
     */
    private $payshopUpdateOrder;

    /**
     * @var PayshopClient
     */
    private $payshopSDK;

    /**
     * @var bool
     */
    private $isCard = false;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->payshopCreateOrder = new PayshopCreateOrder($this->module);
        $this->payshopUpdateOrder = new PayshopUpdateOrder($this->module);
        $this->payshopSDK = PayshopClientFactory::getInstance();
    }

    /**
     * Payment process with credit card
     *
     * @return void
     */
    public function postProcess()
    {
        header('Content-Type: application/json');

        try {
            $instrument = $this->getInstrument();

            $orderStatus = $this->getOrderStatus($instrument);
            $paymentMethod = $instrument['charge']['charge_type'];

            $prestashopOrderId = $this->payshopCreateOrder->execute(
                $paymentMethod,
                $instrument['charge']['id']
            );

            $this->payshopUpdateOrder->execute(
                $paymentMethod,
                $prestashopOrderId,
                $orderStatus,
                $instrument['charge']['id'],
                $instrument['id'],
                $this->getPaymentId($instrument)
            );

            $this->successResponse($instrument, $prestashopOrderId);
        } catch (\Throwable $e) {
            PayshopLog::generate($e->getMessage(), 'error');

            PayshopHelpers::sendErrorWarningByEmail($this->module, $e->getMessage());

            PayshopHelpers::errorResponse($this->module, $e->getMessage(), $this->isCard);
        }
    }

    /**
     * Get instrument information from the request
     *
     * @return array
     */
    public function getInstrument()
    {
        $instrumentId = Tools::getValue('instrumentId');

        if ($instrumentId) {
            $response = $this->payshopSDK->getInstrument($instrumentId);

            if ($response['status'] != 200) {
                throw new \Exception($this->module->l('Error while getting instrument'));
            }

            $this->isCard = true;
            return $response['response'];
        }

        return json_decode(
            file_get_contents('php://input'),
            true
        );
    }

    /**
     * Get order status from the instrument response
     *
     * @param array $instrument
     * @return string
     */
    public function getOrderStatus($instrument)
    {
        $paymentMethod = $instrument['charge']['charge_type'];

        if ($paymentMethod == 'payshop_reference') {
            return 'PAYSHOP_ORDER_STATUS_WAITING_PAYSHOP';
        }

        if ($paymentMethod == 'multibanco') {
            return 'PAYSHOP_ORDER_STATUS_WAITING_MULTIBANCO';
        }

        if ($instrument['status'] === 'pending') {
            return 'PAYSHOP_ORDER_STATUS_WAITING_PAYMENT';
        }

        $issetPayment = isset($instrument['last_payment']);
        $paymentSucess = $issetPayment && $instrument['last_payment']['success'];
        $paymentStatusSucess = $issetPayment && $instrument[ 'last_payment']['status'] == 'success';

        if ($paymentSucess && $paymentStatusSucess) {
            return 'PAYSHOP_ORDER_STATUS_PAID';
        }

        throw new Exception($this->module->l('Instrument status is not valid'));
    }

    /**
     * Get payment id from the instrument response
     *
     * @param array $instrument
     * @return string|null
     */
    private function getPaymentId($instrument)
    {
        if (isset($instrument['last_payment'])) {
            return $instrument['last_payment']['id'];
        }

        return null;
    }

    /**
     * Get Success Response
     *
     * @param string $prestashopOrderId
     * @return string
     */
    private function successResponse($instrument, $prestashopOrderId)
    {
        $successUrl = $this->linkToOrderConfirmationPage();

        if ($this->isCard) {
            Tools::redirect($successUrl);
        }

        $reponse = [
            'status' => 'success',
            'prestashopOrderId' => $prestashopOrderId,
            'successRedirectUrl' => $successUrl
        ];

        $reponse += $this->getFormatedReference($instrument);

        echo json_encode($reponse);
    }

    /**
     * Get formated reference from the instrument for multibanco and payshop
     *
     * @param array $instrument
     * @return array
     */
    private function getFormatedReference($instrument)
    {
        if ($this->notHasReference($instrument)) {
            return [];
        }

        $referenceData = [];

        foreach ($instrument['reference']['fields'] as $key => $item) {
            $referenceData[$item['field']] = $item['value'];
        }

        return $referenceData;
    }

    /**
     * Check if the instrument has a reference
     *
     * @param array $instrument
     * @return boolean
     */
    private function notHasReference($instrument)
    {
        $paymentMethod = $instrument['charge']['charge_type'];

        return $paymentMethod != 'multibanco' && $paymentMethod != 'payshop_reference';
    }

    /**
     * Get link to order confirmation page
     *
     * @return string
     */
    private function linkToOrderConfirmationPage()
    {
        $cart = $this->module->context->cart;
        $cartId = (int) $cart->id;
        $orderId = (int) $this->module->currentOrder;

        $customer = new Customer($cart->id_customer);
        $securityKey = $customer->secure_key;

        $moduloId = (int) $this->module->id;

        return sprintf(
            '%sindex.php?controller=order-confirmation&id_cart=%d&id_module=%d&id_order=%d&key=%s',
            $this->module->context->link->getBaseLink(),
            $cartId,
            $moduloId,
            $orderId,
            $securityKey
        );
    }
}
