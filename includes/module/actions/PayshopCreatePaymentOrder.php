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
     * @var string
     */ 
    private $prestashopOrderId;

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
     * @param int $prestashopOrderId
     * @return int
     * @throws Exception
     */
    public function execute($paymentMethod, $prestashopOrderId)
    {
        $this->paymentMethod = $paymentMethod;
        $this->prestashopOrderId = $prestashopOrderId;

        $this->checkoutIsFilled();
        $this->moduleIsAuthorized();

        return $this->createPaymentOrder();
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
    private function createPaymentOrder()
    {
        $this->isCurrencyEuro();

        $response = $this->payshopSDK->createPaymentOrder(
            $this->getOrderData()
        );

        PayshopHelpers::checkResponse($this->module, $response);
        $this->validateGatewayCurrency($response);

        return $response['response']['order'];
    }

    /**
     * Get payment order data
     * 
     * @return array
     */
    private function getOrderData()
    {
        $data = [
            'amount' => $this->getOrderTotal() * 100,
            'currency' => 'EUR',
            'operative' => 'AUTHORIZATION',
            'service' => PayshopHelpers::getPaymentServiceUUID($this->paymentMethod),
    
            "description" => $this->getDescription(),

            'url_ok' => PayshopHelpers::confirmationPageURL($this->module),
            'url_ko' =>  $this->getProcessFailedRedirectURL(),
            "url_post" => $this->getProcessEventUrl(),
        ];
        
        return array_merge($data, $this->getPaymentData());
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
     * Get order total
     *
     * @return float
     */
    private function getOrderTotal()
    {
        $order = new Order($this->prestashopOrderId);

        return $order->total_paid;
    }

    /**
     * Get description
     *
     * @return string
     */
    private function getDescription()
    {
        return vsprintf(
            '%s %s %s',
            [
                $this->module->context->shop->name,
                $this->module->l(' - order #', 'PayshopCreateCharge'),
                $this->prestashopOrderId
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
        $url = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessEvent'
        );

        return $url;
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
            ['prestashop_order_id' => $this->prestashopOrderId]
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