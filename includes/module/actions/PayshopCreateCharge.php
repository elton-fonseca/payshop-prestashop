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

 class PayshopCreateCharge
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
     * Class constructor
     *
     * @param Module $module
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->payshopSDK = PayshopClientFactory::getInstance();
    }

    /**
     * Send charge and instrument to payshop
     *
     * @param string $paymentMethod
     * @param int $orderId
     * @return int
     * @throws Exception
     */
    public function execute($paymentMethod, $orderId)
    {
        if ($paymentMethod !== 'card') {
            $this->checkoutIsFilled();
            $this->moduleIsAuthorized();
        }

        return $this->createCharge($paymentMethod, $orderId);
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
            throw new Exception($this->module->l('Checkout fields are not filled', 'PayshopCreateCharge'));
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
            throw new Exception($this->module->l('This payment method is not available.', 'PayshopCreateCharge'));
        }
    }

    /**
     * Create charge on payshop
     *
     * @param string $paymentMethod
     * @param int $orderId
     * @return string
     */
    private function createCharge($paymentMethod, $orderId)
    {
        $this->isCurrencyEuro();

        $orderId = $this->getOrderId($paymentMethod, $orderId);
        $total = $this->getOrderTotal($paymentMethod, $orderId);

        $processInstrumentUrl = $this->getProcessInstrumentUrl($orderId);

        $response = $this->payshopSDK->createCharge([
            'charge_type' => $paymentMethod,
            'amount' => (float) $total,
            'currency' => 'EUR',
            'description' => $this->description($orderId),
            'events_url' => $this->getProcessEventUrl(),
            'redirect_url' => $processInstrumentUrl,
            'instrument_params' => $this->getInstrumentParams($paymentMethod)
        ]);

        PayshopHelpers::checkResponse($this->module, $response);

        return $response['response']['id'];
    }

    /**
     * Check if currency is euro
     *
     * @return void
     * @throws Exception
     */
    private function isCurrencyEuro() {
        $currecy = $this->module->context->currency->iso_code;

        if ($currecy != 'EUR') {
            //$total = $total * dd($this->module->context->currency->conversion_rate);
            $message = $this->module->l('Product currency must be EUR', 'PayshopCreateCharge');
            PayshopLog::generate($message, 'error');
            throw new Exception($message);
        }
    }

    /**
     * Get order id
     *
     * @return int
     */
    private function getOrderId($paymentMethod, $orderId)
    {
        if ($paymentMethod !== 'card') {
            return (int) $this->module->currentOrder;
        }

        return $orderId;
    }

    /**
     * Get order total
     *
     * @return float
     */
    private function getOrderTotal($paymentMethod, $orderId)
    {
        if ($paymentMethod !== 'card') {
            return (float) $this->module->context->cart->getOrderTotal(true, Cart::BOTH);
        }

        $order = new Order($orderId);
        return $order->total_paid;
    }

    /**
     * Get description
     *
     * @param int $orderId
     * @return string
     */
    private function description($orderId)
    {
        return vsprintf(
            '%s %s %s',
            [
                $this->module->context->shop->name,
                $this->module->l(' order #', 'PayshopCreateCharge'),
                $orderId
            ]
        );
    }

    /**
     * Get process event url
     *
     * @return string
     */
    private function getProcessEventUrl()
    {
        $processEventURL = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessEvent'
        );

        if (!PayshopHelpers::isHTTPS()) {
            $processEventURL = str_replace(
                'http://127.0.0.1',
                'https://eltonfonseca.dev',
                $processEventURL
            );
        }

        return $processEventURL;
    }

    /**
     * Get process instrument url
     *
     * @param int $orderId
     * @return string
     */
    private function getProcessInstrumentUrl($orderId)
    {
        $processInstrumentURL = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessInstrument',
            [
                'orderId' => $orderId
            ]
        );

        if (!PayshopHelpers::isHTTPS()) {
            $processInstrumentURL = str_replace(
                'http://127.0.0.1',
                'https://eltonfonseca.dev',
                $processInstrumentURL
            );
        }

        return $processInstrumentURL;
    }

    /**
     * Get instrument params for the payment method
     *
     * @param string $paymentMethod
     * @return array
     */
    private function getInstrumentParams($paymentMethod)
    {
        $description = ['description' => $this->module->context->shop->name];

        if ($paymentMethod != 'multibanco' && $paymentMethod != 'payshop_reference') {
            return ['enable3ds' => true] + $description;
        }

        if ($paymentMethod == 'multibanco') {
            $qtdDaysToExpire = (int) Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE_EXPIRATION_DAYS');
        }

        if ($paymentMethod == 'payshop_reference') {
            $qtdDaysToExpire = (int) Configuration::get('PAYSHOP_PAYSHOP_REFERENCE_EXPIRATION_DAYS');
        }

        $expirationdate = date('Y-m-d', strtotime('+' . $qtdDaysToExpire . ' days'));

        return ['end_date' => $expirationdate] + $description;
    }
}