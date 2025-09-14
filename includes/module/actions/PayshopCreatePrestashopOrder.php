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
class PayshopCreatePrestashopOrder
{
    /**
     * @var PaymentModule
     */
    private $module;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * Class constructor
     *
     * @param PaymentModule $module
     */
    public function __construct(PaymentModule $module)
    {
        $this->module = $module;
    }

    /**
     * Create prestashop order and payshop transaction
     *
     * @param string $paymentMethod
     *
     * @return int
     *
     * @throws Exception
     */
    public function execute($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        $prestashopOrderId = $this->createPrestashopOrder();
        $this->createPayshopTransaction();

        return $prestashopOrderId;
    }

    /**
     * Create prestashop order
     *
     * @return int
     *
     * @throws Exception
     */
    private function createPrestashopOrder()
    {
        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);

        $this->module->validateOrder(
            (int) Context::getContext()->cart->id,
            (int) $this->getInitialOrderStatusId(),
            (float) Context::getContext()->cart->getOrderTotal(true, Cart::BOTH),
            $this->formatedPaymentMethodName(),
            null,
            // null,
            [],
            (int) Context::getContext()->currency->id,
            false,
            $customer->secure_key
        );

        $orderId = (int) $this->module->currentOrder;

        if (!$orderId) {
            throw new Exception(PayshopHelpers::errorMessageProcessTransation($this->module, $this->module->l('not created', 'PayshopCreateOrder')));
        }

        return $orderId;
    }

    /**
     * Create payshop transaction
     *
     * @return bool
     *
     * @throws Exception
     */
    private function createPayshopTransaction()
    {
        $isPaymentTest = !Configuration::get('PAYSHOP_PROD_STATUS');

        $transaction = new PayshopTransaction();
        $transaction->where('cart_id', '=', Context::getContext()->cart->id)->destroy();

        $transaction = new PayshopTransaction();
        $isCreated = $transaction->create([
            'cart_id' => Context::getContext()->cart->id,
            'order_id' => $this->module->currentOrder,
            'customer_id' => Context::getContext()->customer->id,
            'total' => Context::getContext()->cart->getOrderTotal(true, Cart::BOTH),
            'payment_method' => $this->paymentMethod,
            'payment_status' => 'pending',
            'is_payment_test' => $isPaymentTest,
        ]);

        if (!$isCreated) {
            throw new Exception(PayshopHelpers::errorMessageProcessTransation($this->module, $this->module->currentOrder));
        }

        return $isCreated;
    }

    /**
     * Get initial order status id
     *
     * @return string|false
     */
    private function getInitialOrderStatusId()
    {
        return Configuration::get('PAYSHOP_ORDER_STATUS_WAITING_PAYMENT');
    }

    /**
     * Formate payment method name
     *
     * @return string
     */
    private function formatedPaymentMethodName()
    {
        $payments = [
            PayshopPaymentMethods::MULTIBANCO => 'Payshop (Multibanco)',
            PayshopPaymentMethods::PAYSHOP_REFERENCE => 'Payshop (Payshop Reference)',
            PayshopPaymentMethods::CREDIT_CARD => 'Payshop (Card)',
            PayshopPaymentMethods::MB_WAY => 'Payshop Online Payments (MBWay)',
            PayshopPaymentMethods::GOOGLEPAY => 'Payshop Online Payments (Google Pay)',
            PayshopPaymentMethods::APPLEPAY => 'Payshop Online Payments (Apple Pay)',
            PayshopPaymentMethods::PAYPAL => 'Payshop Online Payments (Paypal)',
            PayshopPaymentMethods::CLICKTOPAY => 'Payshop Online Payments (Click to Pay)',
        ];

        return $payments[$this->paymentMethod];
    }
}
