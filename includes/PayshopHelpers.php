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
     * Create Payshop event
     *
     * @param string $eventId
     * @param string $eventType
     * @param string $transactionId
     * @return bool
     */
    public static function createEvent($eventId, $eventType, $transactionId)
    {
        $event = new PayshopEventModel();
        $isCreated = $event->create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'transaction_id' => $transactionId
        ]);

        return $isCreated;
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
        if ($response['status'] == 201) {
            return true;
        }

        if ($response['status'] == 401 || $response['status'] == 403) {
            $message = $module->l('Invalid API credentials. Check your credentials in the module settings.', 'PayshopHelpers');

            PayshopLog::generate($message, 'error');

            throw new Exception($message);
        }

        $body = $response['response'];

        if (isset($body['parameters']['number'])) {
            throw new Exception($module->l('Invalid card number', 'PayshopHelpers'));
        }

        if (!Configuration::get('PAYSHOP_PROD_STATUS')) {
            $message = print_r($response, true);

            throw new Exception($message);
        }

        $message = isset($body['message']) ? $body['message'] : $body;
        $message = $message == 'Transaction Error' ? 
                    $module->l('Transaction error, check your payment informations', 'PayshopHelpers') : 
                    $message;

        throw new Exception($message);
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

        $errorUrl = $module->context->link->getBaseLink() .
            'index.php?controller=order&step=3&typeReturn=failure';

        echo json_encode([
            'errorRedirectUrl' => $errorUrl,
            'error' => true,
            'message' => $message
        ]);
        
        http_response_code(400);
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
     * Send email from prestashop
     *
     * @param module $module
     * @param string $message
     * @return void
     */
    public static function sendErrorWarningByEmail($module, $message)
    {
        Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'error_warning', // email template file to be use
            $module->l('Payshop Error Warning', 'PayshopHelpers'), // email subject
            [
                '{message}' => $message // email content
            ],
            Configuration::get('PS_SHOP_EMAIL'), // receiver email address
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL, //file attachment
            NULL //mode smtp
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