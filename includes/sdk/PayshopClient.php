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

require_once 'PayshopRestCli.php';
require_once 'PayshopAbstractClient.php';

class PayshopClient extends PayshopAbstractClient
{
    /**
     * PayshopClient constructor.
     *
     * @param string $apiKey
     * @param string $signature
     * @param bool $isLiveEnvironment
     */
    public function __construct(
        $apiKey,
        $signature,
        $isLiveEnvironment = true,
    ) {
        parent::__construct(
            $apiKey,
            $signature,
            $isLiveEnvironment
        );
    }

    /**
     * Create a new payment order
     *
     * @param array $order
     *
     * @return array
     */
    public function createPaymentOrder($order)
    {
        $response = PayshopRestCli::post(
            $this->getUrl('/payment'),
            $this->addSignature($order),
            $this->getCredentials()
        );

        return $response;
    }

    /**
     * Get payment order by uuid
     *
     * @param array $order
     *
     * @return array
     */
    public function getPaymentOrder($orderUUID)
    {
        $response = PayshopRestCli::get(
            $this->getUrl('/order', $orderUUID),
            $this->getCredentials()
        );

        return $response;
    }

    /**
     * Send the push notification to the user
     *
     * @param array $payment
     *
     * @return array
     */
    public function paymentPush($payment)
    {
        $response = PayshopRestCli::post(
            $this->getUrl('/payment/push'),
            $this->addSignature($payment),
            $this->getCredentials()
        );

        return $response;
    }

    /**
     * Confirm a order payment when is using deffered mode
     *
     * @param array $payment
     *
     * @return array
     */
    public function paymentConfirmation($payment)
    {
        $response = PayshopRestCli::post(
            $this->getUrl('/payment/confirmation'),
            $this->addSignature($payment),
            $this->getCredentials()
        );

        return $response;
    }

    /**
     * Send the wallet payload to the payment gateway
     *
     * @param array $payload
     *
     * @return array
     */
    public function paymentWallet($payload)
    {
        $response = PayshopRestCli::post(
            $this->getUrl('/payment/wallet'),
            $this->addSignature($payload),
            $this->getCredentials()
        );

        return $response;
    }

    /**
     * Cancel a order payment when is using deffered mode
     *
     * @param array $payment
     *
     * @return array
     */
    public function paymentCancellation($payment)
    {
        $response = PayshopRestCli::post(
            $this->getUrl('/payment/cancellation'),
            $this->addSignature($payment),
            $this->getCredentials()
        );

        return $response;
    }

    /**
     * Create transation metadata
     *
     * @param array $payment
     *
     * @return array
     */
    public function createTransationMetadata($transactionUUID, $metadata)
    {
        $response = PayshopRestCli::post(
            $this->getUrl('/transaction', $transactionUUID . '/metadata'),
            $this->addSignature($metadata),
            $this->getCredentials()
        );

        return $response;
    }

    /**
     * Get the client's services by client_uuid
     *
     * @param array $payment
     *
     * @return array
     */
    public function getClientServices($clientUUID)
    {
        $response = PayshopRestCli::get(
            $this->getUrl('/client', $clientUUID . '/services'),
            $this->getCredentials()
        );

        return $response;
    }

    /**
     * get the terminals by terminal_uuid
     *
     * @param array $payment
     *
     * @return array
     */
    public function getTerminals($terminalUUID)
    {
        $response = PayshopRestCli::get(
            $this->getUrl('/terminals', $terminalUUID),
            $this->getCredentials()
        );

        return $response;
    }

    /**
     * Get the URL used to redirect the user to the payment page
     *
     * @param array $payment
     *
     * @return array
     */
    public function getRedirectUrl($token)
    {
        return $this->getUrl('/payment/process', $token);
    }

    /**
     * Get iframe content from the order token
     *
     * @param string $token
     *
     * @return string
     */
    public function getIframeContent($token)
    {
        $connect = curl_init($this->getUrl('/payment/process', $token));

        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'webhook';
        curl_setopt($connect, CURLOPT_USERAGENT, $useragent);

        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($connect, CURLOPT_HTTPHEADER, []);

        return curl_exec($connect);
    }
}
