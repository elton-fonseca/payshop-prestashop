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

class PayshopHelpers
{
    /**
     * Get payment service UUID from the configuration
     *
     * @param string $paymentMethod
     *
     * @return string
     */
    public static function getPaymentServiceUUID($paymentMethod)
    {
        switch ($paymentMethod) {
            case PayshopPaymentMethods::CREDIT_CARD:
                return Configuration::get('PAYSHOP_CARD_SERVICE_UUID');
            case PayshopPaymentMethods::MB_WAY:
                return Configuration::get('PAYSHOP_MBWAY_SERVICE_UUID');
            case PayshopPaymentMethods::PAYSHOP_REFERENCE:
                return Configuration::get('PAYSHOP_REFERENCE_SERVICE_UUID');
            case PayshopPaymentMethods::MULTIBANCO:
                return Configuration::get('PAYSHOP_MBWAY_SERVICE_UUID');
            default:
                return Configuration::get('PAYSHOP_CARD_SERVICE_UUID'); // arrumar isso depois
        }
    }

    /**
     * Get the transaction by column
     *
     * @param string $column
     * @param string|int $value
     *
     * @return array
     *
     * @throws Exception
     */
    public static function getTransacion($column, $value)
    {
        $transaction = new PayshopTransaction();
        $transaction->where($column, '=', $value);
        $transaction = $transaction->get();

        if (!$transaction) {
            throw new Exception('Transaction not found', 404);
        }

        return $transaction;
    }

    /**
     * Check Payshop response
     *
     * @param Module $module
     * @param array $response
     *
     * @return bool
     *
     * @throws Exception
     */
    public static function checkResponse($module, $response)
    {
        if ($response['status'] == 200) {
            return true;
        }

        if ($response['status'] == 401 || $response['status'] == 403) {
            $message = $module->l('PayshopHelpers: Invalid API credentials. Check your credentials on the module settings.', 'payshop');

            PayshopLog::generate($message, 'error');
            throw new Exception($message);
        }

        if ($response['status'] == 400) {
            $message = $module->l('PayshopHelpers: Invalid gateway settings. Check the service UUID on the payment settings.', 'payshop');

            PayshopLog::generate($message, 'error');
            throw new Exception($message);
        }

        throw new Exception($module->l("We couldn't connect to the payment gateway.", 'payshop'));
    }

    /**
     * Set error response
     *
     * @param string $message
     *
     * @return void
     */
    public static function errorResponse($message)
    {
        $context = Context::getContext();

        $context->cookie->__set('redirect_message', $message);

        Tools::redirect('index.php?controller=order&step=3&typeReturn=failure');
    }

    /**
     * Get the confirmation page URL
     *
     * @param Module $module
     * @param string|null $orderId
     *
     * @return string
     */
    public static function confirmationPageURL($module, $orderId = null)
    {
        $context = Context::getContext();
        $cart = $context->cart;
        $cartId = (int) $cart->id;

        // 1. Usa o ID do pedido se ele for passado como parâmetro.
        // 2. Se não, tenta pegar da propriedade 'currentOrder' (de forma segura).
        if (!$orderId && property_exists($module, 'currentOrder') && $module->currentOrder) {
            $orderId = (int) $module->currentOrder;
        }

        // 3. Se ainda não tiver o ID, busca pelo ID do carrinho.
        if (!$orderId) {
            $orderId = (int) Order::getIdByCartId($cartId);
        }

        $customer = new Customer($cart->id_customer);
        $securityKey = $customer->secure_key;

        return $context->link->getPageLink(
            'order-confirmation',
            null,
            null,
            [
                'id_cart' => $cartId,
                'id_module' => $module->id,
                'id_order' => $orderId,
                'key' => $securityKey,
            ]
        );
    }

    /**
     * Get formated exception message
     *
     * @param Module $module
     * @param int|string $prestashopOrderId
     * @param mixed $payshopChargeId
     *
     * @return string
     */
    public static function errorMessageProcessTransation(
        $module, $prestashopOrderId, $payshopChargeId = 'undefined',
    ) {
        return vsprintf(
            $module->l(
                'Error processing transaction. Client order id on your store: %s, Payshop charge id: %s',
                'PayshopHelpers'
            ),
            [$prestashopOrderId, $payshopChargeId]
        );
    }
}
