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

 class PayshopCreatePaymentOrder
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
     * @var string
     */ 
    private $paymentMethod;

    /**
     * List of payment methods that don't need prestashop order before charge
     * @var array
     */
    private $paymentsWithoutOrder = [
        PayshopPaymentMethods::CREDIT_CARD,
        PayshopPaymentMethods::MB_WAY
    ];

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
    public function execute($paymentMethod, $orderId = null)
    {
        $this->paymentMethod = $paymentMethod;

        $this->checkoutIsFilled();
        $this->moduleIsAuthorized();

        return $this->createPaymentOrder($orderId);
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
    private function createPaymentOrder($orderId)
    {
        $this->isCurrencyEuro();

        $orderId = $this->getOrderId($orderId);
        $total = $this->getOrderTotal($orderId);

        $response = $this->payshopSDK->createPaymentOrder(array_merge([
            'amount' => $total * 100,
            'currency' => 'EUR',
            'operative' => 'AUTHORIZATION',
            'service' => $this->getPaymentServiceUUID(),
    
            "description" => $this->getDescription($orderId),

            'url_ok' => $this->getProcessSuccessfulRedirectURL(),
            'url_ko' =>  $this->getProcessFailedRedirectURL(),
            "url_post" => $this->getProcessEventUrl(),
        ], $this->getPaymentData()));

        PayshopHelpers::checkResponse($this->module, $response);
        $this->validateGatewayCurrency($response);

        return $response['response']['order'];
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
    private function getOrderId($orderId)
    {
        if (in_array($this->paymentMethod, $this->paymentsWithoutOrder)) {
            return (int) $this->module->currentOrder;
        }

        return $orderId;
    }

    /**
     * Get order total
     *
     * @return float
     */
    private function getOrderTotal($orderId)
    {
        if (in_array($this->paymentMethod, $this->paymentsWithoutOrder)) {
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
    private function getDescription($orderId)
    {
        return vsprintf(
            '%s %s %s',
            [
                $this->module->context->shop->name,
                $this->module->l(' - order #', 'PayshopCreateCharge'),
                $orderId
            ]
        );
    }

    /**
     * Get payment service UUID from the configuration
     *
     * @param WC_Order $order
     * @param array $references
     * @return void
     */
    public function getPaymentServiceUUID()
    {
        switch ($this->paymentMethod) {
            case PayshopPaymentMethods::CREDIT_CARD:
                return Configuration::get('PAYSHOP_CARD_SERVICE_UUID');
            case PayshopPaymentMethods::MB_WAY:
                return Configuration::get('PAYSHOP_MBWAY_SERVICE_UUID');
            case PayshopPaymentMethods::PAYSHOP_REFERENCE:
                return Configuration::get('PAYSHOP_REFERENCE_SERVICE_UUID');
            case PayshopPaymentMethods::MULTIBANCO:
                return Configuration::get('PAYSHOP_MBWAY_SERVICE_UUID');
        }
    }

    /**
     * Get process event url
     *
     * @return string
     */
    private function getProcessEventUrl()
    {
        return $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessEvent'
        );
    }

    /**
     * Get process instrument url
     *
     * @param int $orderId
     * @return string
     */
    private function getProcessSuccessfulRedirectURL()
    {
        return $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessSuccessfulRedirect',
        );
    }

    /**
     * Get process instrument url
     *
     * @param int $orderId
     * @return string
     */
    private function getProcessFailedRedirectURL()
    {
        return $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessFailedRedirect',
        );
    }

    /**
     * Get payment data
     * 
     * @param string paymentMethod
     * @return array
     */
    private function getPaymentData()
    {
        if ($this->paymentMethod === PayshopPaymentMethods::CREDIT_CARD) {
            return [
                'secure' => "true",
                'save_card' => "false",
            ];
        }

        $options['secure'] = "false";

        if ($this->paymentMethod === PayshopPaymentMethods::MB_WAY) {
            $cart = $this->module->context->cart;
            $client = $cart->id_customer;
            $client = new Customer($cart->id_customer);

            $options['extra_data'] = [
                "profile" => [
                    "first_name" => $client->firstname ? $client->firstname : "",
                    "last_name" => $client->lastname ? $client->lastname : "",
                    "phone" => [
                        "prefix" => Tools::getValue('phone-prefix'),
                        "number" => Tools::getValue('phone-number')
                    ]
                ]
            ];
        }

        return $options;
    }

    /**
     * Validate currency sent by the gateway
     * 
     * @param array $response
     * @return void
     * 
     * @throws Exception
     */
    private function validateGatewayCurrency($response)
    {
        if ($response['response']['order']['currency'] != '978') {
            $message = $this->module->l('Product currency must be EUR', 'payshop');
            PayshopLog::generate($message, PayshopLog::LOG_SEVERITY_ERROR);
            throw new Exception($message);
        }
    }
}