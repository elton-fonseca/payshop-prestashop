<?php

class PayshopProcessWalletPaymentModuleFrontController extends ModuleFrontController
{
    /**
     * @var PayshopClient
     */
    private $payshopSDK;

    /**
     * @var PayshopCreatePaymentOrder
     */
    private $payshopCreatePaymentOrder;

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();
        $this->ajax = true;
        $this->payshopCreatePaymentOrder = new PayshopCreatePaymentOrder($this->module);
        $this->payshopSDK = PayshopClientFactory::getInstance();
    }

    /**
     * Process webhook request sent by the gateway
     *
     * @return void
     */
    public function postProcess()
    {
        try {
            $data = $this->validatedRequest();

            $paymentOrder = $this->payshopCreatePaymentOrder->execute($data['payment_type'], $data['order_id']);

            $response = $this->payshopSDK->paymentWallet([
                'order_uuid' => $paymentOrder['uuid'],
                'wallet' => $data['payment_type'],
                'payload' => $data['payload'],
                'customer_ip' => $_SERVER['REMOTE_ADDR'],
                'flow' => 'WEB',
            ]);

            echo json_encode([
                'redirect' => $response['response']['details']
            ]);
        } catch (\Throwable $e) {
            PayshopHelpers::errorResponse($e->getMessage());
        }
    }

    /**
     * Validate and return the processed data
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    private function validatedRequest()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['order_id'])) {
            throw new Exception('Order ID is required');
        }

        if (!isset($data['payment_type']) || !in_array($data['payment_type'], ['GOOGLEPAY', 'APPLEPAY'])) {
            throw new Exception('Invalid payment type');
        }

        if (!isset($data['payload'])) {
            throw new Exception('Payload is required');
        }

        return [
            'order_id' => $data['order_id'],
            'payment_type' => $data['payment_type'],
            'payload' => $data['payload'],
        ];
    }
}
