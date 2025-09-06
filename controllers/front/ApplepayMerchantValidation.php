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
