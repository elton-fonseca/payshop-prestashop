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

abstract class PayshopAbstractClient
{
    /**
     * @var string
     */
    private const URL_BASE = 'https://popbylink.payshop.pt/v1';

    /**
     * @var string
     */
    private const URL_BASE_TEST = 'https://popbylink.payshop.pt/v1/sandbox';

    /**
     * @var string
     */
    private $isLiveEnvironment = true;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $signature;

    /**
     * PayshopAbstractClient constructor.
     *
     * @param string $apiKey
     * @param string $signature
     * @param string $environment
     *
     * @throws Exception
     */
    public function __construct(
        $apiKey,
        $signature,
        $isLiveEnvironment = true,
    ) {
        $this->isCurlLoaded();

        $this->apiKey = $apiKey;
        $this->signature = $signature;
        $this->isLiveEnvironment = $isLiveEnvironment;

        $this->isCredentilsFilled();
    }

    /**
     * Check if curl is loaded
     *
     * @return void
     *
     * @throws Exception
     */
    private function isCurlLoaded()
    {
        if (!extension_loaded('curl')) {
            throw new Exception('cURL extension is not loaded.');
        }
    }

    /**
     * Check if credentils are filled
     *
     * @return void
     *
     * @throws Exception
     */
    private function isCredentilsFilled()
    {
        $isCredentilsNotFilled = empty($this->apiKey) || empty($this->signature);

        if ($isCredentilsNotFilled) {
            throw new Exception('Credentials are not filled');
        }
    }

    /**
     * get url base for environment
     *
     * @return string
     */
    private function getUrlBase()
    {
        return $this->isLiveEnvironment ? self::URL_BASE : self::URL_BASE_TEST;
    }

    /**
     * get url for endpoint
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function getUrl($endpoint, $resourceId = null)
    {
        $url = $this->getUrlBase() . $endpoint;

        return $resourceId ? $url . '/' . $resourceId : $url;
    }

    /**
     * get public credentials
     *
     * @return array
     */
    protected function getCredentials()
    {
        return [
            'Authorization: Basic ' . base64_encode($this->apiKey . ':'),
        ];
    }

    /**
     * get signature array
     *
     * @return array
     */
    protected function addSignature($data)
    {
        $signature = [
            'signature' => $this->signature,
        ];

        return array_merge($data, $signature);
    }
}
