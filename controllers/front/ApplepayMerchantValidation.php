<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class PayshopApplepayMerchantValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @var PayshopApplepayValidateMerchant
     */
    private $payshopApplepayValidateMerchant;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->payshopApplepayValidateMerchant = new PayshopApplepayValidateMerchant($this->module);
    }

    /**
     * Process webhook request sent by the gateway
     *
     * @return void
     */
    public function postProcess()
    {
        try {
            $validationURL = $this->getValidationUrl();

            $validationData = $this->payshopApplepayValidateMerchant->execute($validationURL);

            echo json_encode($validationData);
        } catch (Throwable $e) {
            PayshopHelpers::errorResponse($e->getMessage());
        }
    }

    /**
     * Validate and return the processed data
     *
     * @param array $data
     *
     * @return string
     *
     * @throws Exception
     */
    private function getValidationUrl()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['validationURL'])) {
            throw new Exception('validationURL is required');
        }

        return $data['validationURL'];
    }
}
