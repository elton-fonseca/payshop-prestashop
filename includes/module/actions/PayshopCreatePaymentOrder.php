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
class PayshopCreatePaymentOrder
{
    /**
     * @var Module
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
     * @var int
     */
    private $prestashopOrderId;

    /**
     * Class constructor
     *
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
        $this->payshopSDK = PayshopClientFactory::getInstance();
    }

    /**
     * Create payment order on the gateway
     *
     * @param string $paymentMethod
     * @param int $prestashopOrderId
     *
     * @return array
     *
     * @throws Exception
     */
    public function execute($paymentMethod, $prestashopOrderId)
    {
        $this->paymentMethod = $paymentMethod;
        $this->prestashopOrderId = $prestashopOrderId;

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
            'amount' => ceil($this->getOrderTotal() * 100),
            'currency' => 'EUR',
            'operative' => 'AUTHORIZATION',
            'service' => PayshopHelpers::getPaymentServiceUUID($this->paymentMethod),

            'description' => $this->getDescription(),

            'url_ok' => PayshopHelpers::confirmationPageURL($this->module),
            'url_ko' => $this->getProcessFailedRedirectURL(),
            'url_post' => $this->getProcessEventUrl(),
        ];

        return array_merge($data, $this->getPaymentData());
    }

    /**
     * Check if currency is euro
     *
     * @return void
     *
     * @throws Exception
     */
    private function isCurrencyEuro()
    {
        $currecy = $this->module->context->currency->iso_code;

        if ($currecy != 'EUR') {
            // $total = $total * dd($this->module->context->currency->conversion_rate);
            $message = $this->module->l('Product currency must be EUR', 'PayshopCreateCharge');
            PayshopLog::generate('PayshopCreatePaymentOrder: ' . $message, 'error');
            throw new Exception($message);
        }
    }

    /**
     * Get order total value
     *
     * @return float
     */
    private function getOrderTotal()
    {
        $order = new Order((int) $this->prestashopOrderId);

        return $order->total_paid;
    }

    /**
     * Get order description
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
                $this->prestashopOrderId,
            ]
        );
    }

    /**
     * Get process failed redirect url
     *
     * @return string
     */
    private function getProcessFailedRedirectURL()
    {
        // return $this->module->context->link->getModuleLink(
        return Context::getContext()->link->getModuleLink(
            $this->module->name,
            'ProcessFailedRedirect',
            ['prestashop_order_id' => $this->prestashopOrderId]
        );
    }

    /**
     * Get process event url
     *
     * @return string
     */
    private function getProcessEventUrl()
    {
        // return $this->module->context->link->getModuleLink(
        return Context::getContext()->link->getModuleLink(
            $this->module->name,
            'ProcessEvent'
        );
    }

    /**
     * Get payment data
     *
     * @return array
     */
    private function getPaymentData()
    {
        if ($this->paymentMethod === PayshopPaymentMethods::CREDIT_CARD) {
            return [
                'secure' => true,
                'save_card' => false,
            ];
        }

        if ($this->paymentMethod === PayshopPaymentMethods::GOOGLEPAY) {
            return [
                'secure' => true,
            ];
        }

        $options['secure'] = false;

        if ($this->paymentMethod === PayshopPaymentMethods::MB_WAY) {
            // $cart = $this->module->context->cart;
            $cart = Context::getContext()->cart;
            $client = $cart->id_customer;
            $client = new Customer($cart->id_customer);

            $options['extra_data'] = [
                'profile' => [
                    'first_name' => $client->firstname ? $client->firstname : '',
                    'last_name' => $client->lastname ? $client->lastname : '',
                    'phone' => [
                        'prefix' => Tools::getValue('phone-prefix'),
                        'number' => Tools::getValue('phone-number'),
                    ],
                ],
            ];
        }

        return $options;
    }

    /**
     * Validate currency sent by the gateway
     *
     * @param array $response
     *
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
