<?php

class PayshopHelpers
{
    /**
     * Get payment service UUID from the configuration
     *
     * @param string $paymentMethod
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
        }
    }

    /**
     * Get the transaction by column
     *
     * @param string $column
     * @param string $value
     * @return array
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
     * @param module $module
     * @param array $response
     * @return bool
     * @throws Exception
     */
    public static function checkResponse($module, $response)
    {
        if ($response['status'] == 200) {
            return true;
        }

        if ($response['status'] == 401 || $response['status'] == 403) {
            $message = $module->l('Invalid API credentials. Check your credentials on the module settings.', 'payshop');

            PayshopLog::generate($message, 'error');
            throw new Exception($message);
        }

        if ($response['status'] == 400) {
            $message = $module->l('Invalid gateway settings. Check the service UUID on the payment settings.', 'payshop');

            PayshopLog::generate($message, 'error');
            throw new Exception($message);
        }

        throw new Exception($module->l("We couldn't connect to the payment gateway.", 'payshop'));
    }

    /**
     * Set error response
     *
     * @param string $message
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
     * @return array
     */
    public static function confirmationPageURL($module, $orderId = null)
    {
        $context = Context::getContext();

        $cart = $context->cart;
        $cartId = (int) $cart->id;
        $orderId = (int) $module->currentOrder;
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
                'key' => $securityKey
            ]
        );    
    }

    /**
     * Get formated exception message
     * 
     * @param module $module
     * @param int $prestashopOrderId
     * @param string $payshopChargeId
     * @return string
     */
    public static function errorMessageProcessTransation(
        $module, $prestashopOrderId, $payshopChargeId = 'undefined'
    )
    {
        return vsprintf(
            $module->l(
                'Error processing transaction. Client order id on your store: %s, Payshop charge id: %s',
                'PayshopHelpers'
            ),
            [$prestashopOrderId, $payshopChargeId]
        );
    }
}