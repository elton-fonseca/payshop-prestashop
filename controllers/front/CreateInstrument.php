<?php
/**
 * 2007-2022 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

class PayshopCreateInstrumentModuleFrontController extends ModuleFrontController
{
    /**
     * @var PayshopCreateInstrument
     */
    private $payshopCreateInstrument;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->payshopCreateInstrument = new PayshopCreateInstrument($this->module);
    }

    /**
     * Payment process with credit card
     *
     * @return void
     */
    public function postProcess()
    {
        header('Content-Type: application/json');

        try {
            $instrumentData = json_decode(
                file_get_contents('php://input'),
                true
            );

            $this->validadeInstrumentData($instrumentData);

            unset($instrumentData['paymentType']);

            $instrument = $this->payshopCreateInstrument->execute($instrumentData);

            echo json_encode($instrument);
        } catch (\Exception $e) {
            PayshopHelpers::errorResponse($this->module, $e->getMessage());
        }
    }

    /**
     * Validate instrument data
     *
     * @param array $instrumentData
     * @return void
     * @throws Exception
     */
    private function validadeInstrumentData($instrumentData)
    {
        $this->validateChargeAndPaymentType($instrumentData);

        if ($instrumentData['paymentType'] == 'card') {
            $this->validateCardData($instrumentData);
        }

        if ($instrumentData['paymentType'] == 'mbway') {
            $this->validateMBWayData($instrumentData);
        }
    }

    /**
     * Validate card data
     *
     * @return void
     * @throws \Exception
     */
    private function validateCardData($cardData)
    {
        if (!$cardData['number']) {
            throw new Exception(
                $this->module->l('Card number is required')
            );
        }

        if (strlen($cardData['number']) != 16) {
            throw new Exception(
                $this->module->l('Card number is invalid')
            );
        }

        if (!$cardData['expiration_month']) {
            throw new Exception(
                $this->module->l('Card expiration is required')
            );
        }

        if (strlen($cardData['expiration_month']) < 2) {
            throw new Exception(
                $this->module->l('Card expiration is invalid')
            );
        }

        if (!$cardData['expiration_year']) {
            throw new Exception(
                $this->module->l('Card expiration is required')
            );
        }

        if (strlen($cardData['expiration_year']) < 4) {
            throw new Exception(
                $this->module->l('Card expiration is invalid')
            );
        }

        if (!$cardData['cvc']) {
            throw new Exception(
                $this->module->l('Card security code is required')
            );
        }

        if (strlen($cardData['cvc']) != 3) {
            throw new Exception(
                $this->module->l('Card security code is invalid')
            );
        }

        if (!$cardData['name']) {
            throw new Exception(
                $this->module->l('Card holder name is required')
            );
        }
    }

    /**
     * Validate MBWay data
     *
     * @return void
     * @throws \Exception
     */
    private function validateMBWayData($mbwayData)
    {
        if (!$mbwayData['phone']) {
            throw new Exception(
                $this->module->l('Phone number is required')
            );
        }

        if (strlen($mbwayData['phone']) < 9) {
            throw new Exception(
                $this->module->l('Phone number is invalid')
            );
        }
    }

    /**
     * Validate charge and payment type
     *
     * @return void
     * @throws \Exception
     */
    private function validateChargeAndPaymentType($instrumentData)
    {
        if (!$instrumentData['charge']) {
            throw new Exception(
                $this->module->l('Charge id is required')
            );
        }

        if (!$instrumentData['paymentType']) {
            throw new Exception(
                $this->module->l('Payment type is required')
            );
        }
    }  
}

