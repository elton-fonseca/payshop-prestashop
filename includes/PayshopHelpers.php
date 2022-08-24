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
            throw new Exception(
                $module->l('Invalid API credentials. Check your credentials in the module settings.')
            );
        }

        $body = $response['response'];

        if (isset($body['parameters']['number'])) {
            throw new Exception($module->l('Invalid card number'));
        }

        $message = isset($body['message']) ? $body['message'] : $body;

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
     * Send email from prestashop
     *
     * @param string $message
     * @return voi
     */
    public static function sendEmail($message)
    {
        Mail::Send(
            (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
            'waiting_payment_multibanco', // email template file to be use
            'Payshop Error Warning', // email subject
            [
                '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address
                '{message}' => 'Hello world' // email content
            ],
            'elton869@gmail.com', // receiver email address
            NULL, //receiver name
            NULL, //from email address
            NULL,  //from name
            NULL, //file attachment
            NULL //mode smtp
        );
    }
}