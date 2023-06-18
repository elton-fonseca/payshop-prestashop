<?php

abstract class PayshopAbstractClient
{
    /**
     * @var string
     */
    private const URL_BASE = 'https://switch-processing.teya.com/v2';

    /**
     * @var string
     */
    private const URL_BASE_TEST = 'https://switch-processing.teya.xyz/v2';

    /**
     * @var string
     */
    private $isLiveEnvironment = true;

    /**
     * @var string
     */
    private $publicKeyApi;

    /**
     * @var string
     */
    private $accountId;

    /**
     * @var string
     */
    private $secretKeyApi;

    /**
     * PayshopAbstractClient constructor.
     * 
     * @param string $publicKeyApi
     * @param string $secretKeyApi
     * @param string $accountId
     * @param string $environment
     * @throws Exception 
     */
    public function __construct(
        $publicKeyApi,
        $secretKeyApi,
        $accountId,
        $isLiveEnvironment = true
    ) {
        $this->isCurlLoaded();

        $this->publicKeyApi = $publicKeyApi;
        $this->secretKeyApi = $secretKeyApi;
        $this->accountId = $accountId;
        $this->isLiveEnvironment = $isLiveEnvironment;

        $this->isCredentilsFilled();
    }

    /**
     * Check if curl is loaded
     * 
     * @return void 
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
     * @throws Exception 
     */
    private function isCredentilsFilled()
    {
        $isCredentilsNotFilled = empty($this->publicKeyApi) || empty($this->secretKeyApi) || empty($this->accountId);

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
    protected function getPublicCredentials()
    {
        return [
            'Authorization: Basic ' . base64_encode($this->publicKeyApi . ':'),
        ];
    }

    /**
     * get secret credentials
     * 
     * @return array
     */
    protected function getSecretCredentials()
    {
        return [
            'Authorization: Basic ' . base64_encode($this->accountId . ':' . $this->secretKeyApi),
        ];
    }
}