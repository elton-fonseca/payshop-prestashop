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
class PayshopProcessMBWayModuleFrontController extends ModuleFrontController
{
    /**
     * @var PayshopCreateOrder
     */
    private $payshopCreateOrder;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->payshopCreateOrder = new PayshopCreateOrder($this->module);
    }

    /**
     * Payment process with MBWay
     *
     * @return void
     */
    public function postProcess()
    {
        try {
            $this->phoneValidation();

            [$_, $paymentOrder] = $this->payshopCreateOrder->execute(
                PayshopPaymentMethods::MB_WAY
            );

            Tools::redirect(
                PayshopClientFactory::getInstance()->getRedirectUrl($paymentOrder['token']) . '?apm=MBWAY'
            );
        } catch (Throwable $e) {
            PayshopHelpers::errorResponse($e->getMessage());
        }
    }

    /**
     * Validate the phone number
     *
     * @return void
     *
     * @throws Exception
     */
    public function phoneValidation()
    {
        $mbwayprefix = trim(Tools::getValue('phone-prefix', ''));
        $mbwayPhone = trim(Tools::getValue('phone-number', ''));

        if (strlen($mbwayprefix) < 1 || strlen($mbwayprefix) > 4 || strlen($mbwayPhone) < 4) {
            throw new Exception($this->module->l('Phone number is invalid', 'ProcessMBWay'));
        }
    }
}
