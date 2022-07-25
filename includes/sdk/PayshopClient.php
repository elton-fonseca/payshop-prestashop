<?php

require_once 'PayshopRestCli.php';
require_once 'PayshopAbstractClient.php';

class PayshopClient extends PayshopAbstractClient
{
    /**
     * PayshopClient constructor.
     * 
     * @param string $publicKeyApi
     * @param string $secretKeyApi
     * @param string $accountId
     * @param bool $isLiveEnvironment
     */
    public function __construct(
        $publicKeyApi,
        $secretKeyApi,
        $accountId,
        $isLiveEnvironment = true
    ) {
        parent::__construct(
            $publicKeyApi,
            $secretKeyApi,
            $accountId,
            $isLiveEnvironment
        );
    }

    /**
     * Create a new charge
     *
     * @param array $charge
     * @return array
     */
    public function createCharge($charge)
    {
        $response = PayshopRestCli::post(
            $this->getUrl('/charges'),
            $charge,
            $this->getSecretCredentials()
        );

        return $response;
    }

    /**
     * Create a new instrument
     *
     * @param array $chargeId
     * @return array
     */
    public function createInstrument($instrument)
    {
        $response = PayshopRestCli::post(
            $this->getUrl('/instruments'),
            $instrument,
            $this->getPublicCredentials()
        );

        return $response;
    }

    /**
     * Create a new refund
     *
     * @param array $refund
     * @return array
     */
    public function createRefund($refund)
    {
        $response = PayshopRestCli::post(
            $this->getUrl('/refunds'),
            $refund,
            $this->getSecretCredentials()
        );

        return $response;
    }

    /**
     * Get charge by id
     *
     * @param array $chargeId
     * @return array
     */
    public function getCharge($chargeId)
    {
        $response = PayshopRestCli::get(
            $this->getUrl('/charges', $chargeId),
            $this->getSecretCredentials()
        );

        return $response;
    }

    /**
     * Get instrument by id
     *
     * @param int $instrumentId
     * @return array
     */
    public function getInstrument($instrumentId)
    {
        $response = PayshopRestCli::get(
            $this->getUrl('/instruments', $instrumentId),
            $this->getSecretCredentials()
        );

        return $response;
    }

    /**
     * Get payment by id
     *
     * @param int $paymentId
     * @return array
     */
    public function getPayment($paymentId)
    {
        $response = PayshopRestCli::get(
            $this->getUrl('/payments', $paymentId),
            $this->getSecretCredentials()
        );

        return $response;
    }

    /**
     * Get event by id
     *
     * @param int $eventId
     * @return array
     */
    public function getEvent($eventId = null)
    {
        $response = PayshopRestCli::get(
            $this->getUrl('/events', $eventId),
            $this->getSecretCredentials()
        );

        return $response;
    }

    /**
     * Get refund by id
     *
     * @param int $refundId
     * @return array
     */
    public function getRefund($refundId = null)
    {
        $response = PayshopRestCli::get(
            $this->getUrl('/refunds', $refundId),
            $this->getSecretCredentials()
        );

        return $response;
    }

}