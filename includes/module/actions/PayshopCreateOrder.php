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

 class PayshopCreateOrder
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
     * Create prestashop order and payshop transaction
     *
     * @param string $paymentMethod
     * @return int
     * @throws Exception
     */
    public function execute($paymentMethod)
    {
        $this->checkoutIsFilled();
        $this->moduleIsAuthorized();

        $orderId = $this->createPrestashopOrder($paymentMethod);

        $this->createPayshopTransaction($orderId, $paymentMethod);

        return $orderId;
    }

    /**
     * Checkout if all informations are filled on checkout page
     *
     * return void
     */
    private function checkoutIsFilled()
    {
        $cart = $this->module->context->cart;

        $moduleDisabed = !$this->module->active;
        $cartIsEmpty = !$cart->id;
        $clientNotFilled = $cart->id_customer == 0;
        $deliveryAddressNotFilled = $cart->id_address_delivery == 0;
        $invoiceAddressNotFilled = $cart->id_address_invoice == 0;

        if (
            $moduleDisabed || $cartIsEmpty || $clientNotFilled || 
            $deliveryAddressNotFilled || $invoiceAddressNotFilled
        ) {
            throw new Exception($this->module->l('Checkout fields are not filled'));
        }
    }

    /**
     * Check if module is authorized
     *
     * return void
     * @throws Exception
     */
    private function moduleIsAuthorized()
    {
        $authorized = false;

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'payshop') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            throw new Exception($this->module->l('This payment method is not available.'));
        }
    }

    /**
     * Create prestashop order
     *
     * @return int
     * @throws Exception
     */
    private function createPrestashopOrder($paymentMethod)
    {
        $waitingPaymentOrderStatusID = Configuration::get('PAYSHOP_ORDER_STATUS_WAITING_PAYMENT');

        $cart = $this->module->context->cart;
        $customer = new Customer($cart->id_customer);
        
        $this->module->validateOrder(
            (int) $this->module->context->cart->id,
            $waitingPaymentOrderStatusID,
            (float) $this->module->context->cart->getOrderTotal(true, Cart::BOTH),
            $this->formatedPaymentMethodName($paymentMethod),
            null,
            null,
            (int)$this->module->context->currency->id,
            false,
            $customer->secure_key
        );

        $orderId = (int) $this->module->currentOrder;

        if (!$orderId) {
            PayshopLog::generate(
                $this->module->l('Error while creating order prestashop order.')
            );

            throw new Exception($this->module->l('Error creating order'));
        }

        return $orderId;
    }

    /**
     * Create payshop transaction
     * 
     * @param int $orderId
     * @param string $paymentMethod
     * @return bool
     * @throws Exception
     */
     private function createPayshopTransaction($orderId, $paymentMethod)
     {
        $isPaymentTest = !Configuration::get('PAYSHOP_PROD_STATUS');

        $transaction = new PayshopTransaction();
        $isCreated = $transaction->create([
            'cart_id' => $this->module->context->cart->id,
            'order_id' => $orderId,
            'customer_id' => $this->module->context->customer->id,
            'total' => $this->module->context->cart->getOrderTotal(true, Cart::BOTH),
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
            'is_payment_test' => $isPaymentTest
        ]);

        if (!$isCreated) {
            PayshopLog::generate(
                $this->module->l('Error while creating payshop transaction in database.')
            );

            throw new Exception($this->module->l('Error creating transaction'));
        }

        return $isCreated;
     }

     public function formatedPaymentMethodName($paymentMethod)
     {
        $payments = [
            'multibanco' => $this->module->l('Payshop (Multibanco)'),
            'payshop_reference' => $this->module->l('Payshop (Payshop Reference)'),
            'card' => $this->module->l('Payshop (Card)'),
            'mbway' => $this->module->l('Payshop (MBWay)')
        ];

        return $payments[$paymentMethod];
     }
 }