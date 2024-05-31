<?php

class PayshopHelpers
{
    /**
     * Get the transaction by column
     *
     * @param string $chargeId
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
     * @return void
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
     * @param module $module
     * @param string $message
     * @return void
     */
    public static function errorResponse($module, $message)
    {
        $module->context->cookie->__set('redirect_message', $message);

        Tools::redirect('index.php?controller=order&step=3&typeReturn=failure');
    }

    /**
     * Get the instrument params
     *
     * @param Module $module
     * @param string|null $orderId
     * @return array
     */
    public static function confirmationPageURL($module, $orderId = null)
    {
        if ($orderId) {
            $order = new Order($orderId);
            $cartId = $order->id_cart;
            $cart = new Cart((int) $cartId);
            $securityKey = $cart->secure_key;
        } else {
            $cart = $module->context->cart;
            $cartId = (int) $cart->id;
            $orderId = (int) $module->currentOrder;
            $customer = new Customer($cart->id_customer);
            $securityKey = $customer->secure_key;
        }

        return $module->context->link->getPageLink(
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
     * @param module $module
     * @param string $prestashopOrderId
     * @param string $payshopChargeId
     * @return string
     */
    public static function errorMessageProcessTransation(
        $module, $prestashopOrderId, $payshopChargeId
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

    /**
     * Check is the store is in https
     * 
     * @return bool
     */
    public static function isHTTPS()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    }


}