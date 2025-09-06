<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PayshopPaymentServiceUUID
{
    /**
     * @var PayshopClient
     */
    private $payshopSDK;

    /**
     * @var array
     */
    private $availableServices = [];

    /**
     * Add links to plugin list
     * 
     * @return void
     */
    public function execute()
    {

        $this->payshopSDK = PayshopClientFactory::getInstance();

        $this->availableServices = $this->getGatewayServices();

        $this->fillServiceUUID('CREDORAX', 'PAYSHOP_CARD_SERVICE_UUID');
        $this->fillServiceUUID('CREDORAX', 'PAYSHOP_GOOGLEPAY_SERVICE_UUID');
        $this->fillServiceUUID('CREDORAX', 'PAYSHOP_APPLEPAY_SERVICE_UUID');

        $this->fillServiceUUID('PAYSHOP', 'PAYSHOP_REFERENCE_SERVICE_UUID');

        $this->fillServiceUUID('SIBS', 'PAYSHOP_MBWAY_SERVICE_UUID', function($service) {
            $response = $this->payshopSDK->getTerminals($service['terminal']);

            return isset($response['response']['terminal']['credentials']['is_mbway_enabled']) ?
                        $response['response']['terminal']['credentials']['is_mbway_enabled'] :
                        false;
        });

        $this->fillServiceUUID('SIBS', 'PAYSHOP_MULTIBANCO_REFERENCE_SERVICE_UUID', function($service) {
            $response = $this->payshopSDK->getTerminals($service['terminal']);
            
            return isset($response['response']['terminal']['credentials']['is_multibanco_enabled']) ?
                        $response['response']['terminal']['credentials']['is_multibanco_enabled'] :
                        false;
        });
    }

    /**
     * Fill service UUID
     * 
     * @param string $serviceType
     * @param string $serviceStorageKey
     * @param callable $aditionalChecking
     * @return void
     */
    private function fillServiceUUID($serviceType, $serviceStorageKey, $aditionalChecking = false)
    {
       $service = $this->getService($serviceType, $this->availableServices);

        if (!$service) {
            return;
        }

        if (!$service['enabled']) {
            return;
        }

        $checkResult = true;
        if ($aditionalChecking) {
            $checkResult = $aditionalChecking($service);
        }

        if (!$checkResult) {
            return;
        }

        Configuration::updateValue($serviceStorageKey, $service['uuid']);
    }

    /**
     * Get service UUID
     * 
     * @return array
     */
    private function getGatewayServices()
    {
        $response = $this->payshopSDK->getClientServices(
            PayshopClientFactory::getClientUUID()
        );

        if ($response['status'] !== 200) {
            throw new Exception();
        }

        return $response['response']['services'];
    }

    /**
     * Get service
     * 
     * @param string $serviceType
     * @param array $services
     * 
     * @return array
     */
    private function getService($serviceType, $services)
    {
        foreach ($services as $service) {
            if ($service['type'] === $serviceType) {
                return $service;
            }
        }
    }
}