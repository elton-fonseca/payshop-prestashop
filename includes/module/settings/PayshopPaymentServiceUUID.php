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

        $this->fillServiceUUID('SIBS', 'PAYSHOP_MBWAY_SERVICE_UUID', function ($service) {
            $response = $this->payshopSDK->getTerminals($service['terminal']);

            return isset($response['response']['terminal']['credentials']['is_mbway_enabled']) ?
                        $response['response']['terminal']['credentials']['is_mbway_enabled'] :
                        false;
        });

        $this->fillServiceUUID('SIBS', 'PAYSHOP_MULTIBANCO_REFERENCE_SERVICE_UUID', function ($service) {
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
     *
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
