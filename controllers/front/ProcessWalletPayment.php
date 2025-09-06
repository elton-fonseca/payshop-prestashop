<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
/*
 * 2007-2025 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2025 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class PayshopProcessWalletPaymentModuleFrontController extends ModuleFrontController
{
    /**
     * @var PayshopClient
     */
    private $payshopSDK;

    /**
     * @var PayshopCreateWalletPayment
     */
    private $payshopCreateWalletPayment;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->payshopCreateWalletPayment = new PayshopCreateWalletPayment($this->module);
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

            $result = $this->payshopCreateWalletPayment->execute($data);

            if ($data['payment_type'] === 'GOOGLEPAY') {
                $redirect = $result;
            } else {
                $redirect = PayshopHelpers::confirmationPageURL($this->module);
            }

            echo json_encode([
                'success' => (bool) $result,
                'redirect' => $redirect,
            ]);
        } catch (Throwable $e) {
            PayshopHelpers::errorResponse($e->getMessage());
        }
    }

    /**
     * Validate and return the processed data
     *
     * @param array $data
     *
     * @return array
     *
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
