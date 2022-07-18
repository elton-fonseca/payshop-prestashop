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

 class SendOrderToPayshop
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
     * @return int
     * @throws Exception
     */
    public function execute($paymentMethod, $instrumentData = [])
    {
        $chargeId = $this->createCharge($paymentMethod);

        $instrumentData = $instrumentData + [
            'charge' => $chargeId,
            'name' => 'Default instrument'
        ];

        return $this->createInstrument($instrumentData);
    }

    /**
     * Create charge on payshop
     *
     * @param string $paymentMethod
     * @return string
     */
    private function createCharge($paymentMethod)
    {
        $orderId = (int) $this->module->currentOrder;
        $total = (float) $this->module->context->cart->getOrderTotal(true, Cart::BOTH);

        $webHookProcessURL = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessEvent'
        );

        $response = $this->payshopSDK->createCharge([
            'charge_type' => $paymentMethod,
            'amount' => (float) $total,
            'currency' => 'EUR',
            'description' => $this->module->l('PrestaShop order #') . $orderId,
            'events_url' => str_replace('http://127.0.0.1', 'https://teste.com', $webHookProcessURL),
        ]);

        $this->checkResponse($response, 'charge');

        return $response['response']['id'];
    }

    /**
     * Create instrument on payshop
     *
     * @param array $instrumentData
     * @return array
     */
    private function createInstrument($instrumentData)
    {
        $response = $this->payshopSDK->createInstrument($instrumentData);

        $this->checkResponse($response, 'instrument');

        return $response['response'];
    }

    /**
     * Check Payshop response
     *
     * @param array $response
     * @param string $type
     * @return void
     * @throws Exception
     */
    private function checkResponse($response, $type)
    {
        if ($response['status'] == 201) {
            return true;
        }

        if ($response['status'] == 401 || $response['status'] == 403) {
            $message = $this->module->l('Invalid API credentials. Check your credentials in the module settings.');

        }

        if (isset($response['response']['parameters']['number'])) {
            $message = $this->module->l('Invalid card number');
        }

        if (isset($message)) {
            PayshopLog::generate($message, 'error');
            $this->module->context->cookie->__set('redirect_message', $message);
            throw new Exception($message);
        }

        $responseMessage = isset($response['response']['message']) ?
                                    $response['response']['message'] : 
                                    $response['response'];

        $message = $this->module->l('Error creating ' . $type . 
                                    ': Status Code:' . $response['status'] .
                                    ' Message: ' . $responseMessage);

        if (isset($response['response']['parameters']['phone'])) {
            $message = $this->module->l('Invalid phone number');
        }

        throw new Exception($message);
    }
}