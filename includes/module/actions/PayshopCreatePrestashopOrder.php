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

 class PayshopCreatePrestashopOrder
 {
    /**
     * @var Modulo
     */
    private $module;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $paymentOrderId;

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
     * Create prestashop order and payshop transaction
     *
     * @param string $paymentMethod
     * @param int $paymentOrderId
     * @return int
     * @throws Exception
     */
    public function execute($paymentMethod, $paymentOrderId = 'undefined')
    {
        $this->paymentMethod = $paymentMethod;
        $this->paymentOrderId = $paymentOrderId;
        
        $prestashopOrderId = $this->createPrestashopOrder();

        $this->createPayshopTransaction($prestashopOrderId);

        return $prestashopOrderId;
    }

    /**
     * Create prestashop order
     *
     * @param string $paymentMethod
     * @param string $chargeId
     * @return int
     * @throws Exception
     */
    private function createPrestashopOrder()
    {
        $cart = $this->module->context->cart;
        $customer = new Customer($cart->id_customer);
        
        $this->module->validateOrder(
            (int) $this->module->context->cart->id,
            (int) $this->getInitialOrderStatusId(),
            (float) $this->module->context->cart->getOrderTotal(true, Cart::BOTH),
            $this->formatedPaymentMethodName($this->paymentMethod),
            null,
            null,
            (int)$this->module->context->currency->id,
            false,
            $customer->secure_key
        );

        $orderId = (int) $this->module->currentOrder;

        if (!$orderId) {
            throw new Exception(
                PayshopHelpers::errorMessageProcessTransation(
                    $this->module,
                    $this->module->l('not created', 'PayshopCreateOrder'),
                    $this->paymentOrderId
                )
            );
        }

        return $orderId;
    }

    /**
     * Create payshop transaction
     * 
     * @param int $prestashopOrderId
     * @return bool
     * @throws Exception
     */
     private function createPayshopTransaction($prestashopOrderId)
     {
        $isPaymentTest = !Configuration::get('PAYSHOP_PROD_STATUS');

        $transaction = new PayshopTransaction();
        $isCreated = $transaction->create([
            'cart_id' => $this->module->context->cart->id,
            'order_id' => $prestashopOrderId,
            'customer_id' => $this->module->context->customer->id,
            'total' => $this->module->context->cart->getOrderTotal(true, Cart::BOTH),
            'payment_method' => $this->paymentMethod,
            'payment_status' => 'pending',
            'is_payment_test' => $isPaymentTest
        ]);

        if (!$isCreated) {
            throw new Exception(
                PayshopHelpers::errorMessageProcessTransation(
                    $this->module,
                    $prestashopOrderId,
                    $this->paymentOrderId
                )
            );
        }

        return $isCreated;
     }

    /**
     * Get initial order status id
     * 
     * @param string $paymentMethod
     * @return int
     */
     private function getInitialOrderStatusId()
     {
        return Configuration::get('PAYSHOP_ORDER_STATUS_WAITING_PAYMENT');
     }

    /**
     * Formate payment method name
     * 
     * @param string $paymentMethod
     * @return string
     */
     private function formatedPaymentMethodName($paymentMethod)
     {
        $payments = [
            PayshopPaymentMethods::MULTIBANCO => 'Payshop (Multibanco)',
            PayshopPaymentMethods::PAYSHOP_REFERENCE => 'Payshop (Payshop Reference)',
            PayshopPaymentMethods::CREDIT_CARD => 'Payshop (Card)',
            PayshopPaymentMethods::MB_WAY => 'Payshop Online Payments (MBWay)'
        ];

        return $payments[$paymentMethod];
     }
 }