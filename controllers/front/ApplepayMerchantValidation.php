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
