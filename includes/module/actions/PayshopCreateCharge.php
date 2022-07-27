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
     * @return int
     * @throws Exception
     */
    public function execute($paymentMethod)
    {
        return $this->createCharge($paymentMethod);
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

        $processInstrumentURL = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessInstrument'
        );

        $shopName = $this->module->context->shop->name;

        $response = $this->payshopSDK->createCharge([
            'charge_type' => $paymentMethod,
            'amount' => (float) $total,
            'currency' => 'EUR',
            //'currency' => $this->module->context->currency->iso_code,
            'description' => $shopName . $this->module->l(' order #') . $orderId,
            'events_url' => str_replace('http://127.0.0.1', 'https://eltonfonseca.dev', $webHookProcessURL),
            'instrument_params' => $this->getInstrumentParams($paymentMethod),
            'redirect_url' => str_replace('http://127.0.0.1', 'https://eltonfonseca.dev', $processInstrumentURL)
        ]);

        $this->checkResponse($response, 'charge');

        return $response['response']['id'];
    }

    /**
     * Get instrument params for the payment method
     *
     * @param string $paymentMethod
     * @return array
     */
    private function getInstrumentParams($paymentMethod)
    {
        if ($paymentMethod != 'multibanco' && $paymentMethod != 'payshop_reference') {
            return ['enable3ds' => true];
        }

        if ($paymentMethod == 'multibanco') {
            $qtdDaysToExpire = (int) Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE_EXPIRATION_DAYS');
        }

        if ($paymentMethod == 'payshop_reference') {
            $qtdDaysToExpire = (int) Configuration::get('PAYSHOP_PAYSHOP_REFERENCE_EXPIRATION_DAYS');
        }

        return ['end_date' => date('Y-m-d', strtotime('+' . $qtdDaysToExpire . ' days'))];
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
        } else {
            $responseMessage = isset($response['response']['message']) ?
                $response['response']['message'] :
                $response['response'];

            $message = $this->module->l('Error creating ' . $type .
                ': Status Code:' . $response['status'] .
                ' Message: ' . $responseMessage);
        }

        PayshopLog::generate($message, 'error');
        $this->module->context->cookie->__set('redirect_message', $message);
        throw new Exception($message);
    }
}