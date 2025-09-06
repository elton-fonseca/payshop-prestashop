<?php
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
     * @throws Exception 
     */
    public function __construct(
        $apiKey,
        $signature,
        $isLiveEnvironment = true
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
            'Authorization: Basic ' . base64_encode($this->apiKey . ':')
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
            'signature' => $this->signature
        ];

        return array_merge($data, $signature);
    }
}