<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
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

class PayshopProcessPayshopModuleFrontController extends ModuleFrontController
{
/**
     * @var PayshopCreateOrder
     */
    private $payshopCreateOrder;

    /**
     * @var PayshopUpdateOrder
     */
    private $payshopUpdateOrder;   

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->payshopCreateOrder = new PayshopCreateOrder($this->module);
        $this->payshopUpdateOrder = new PayshopUpdateOrder($this->module);
    }

    /**
     * Payment process with credit card
     *
     * @return void
     */
    public function postProcess()
    {
        try {
            [$prestashopOrderId, $paymentOrder] = $this->payshopCreateOrder->execute(
                PayshopPaymentMethods::PAYSHOP_REFERENCE
            );

            $this->saveIframeContent($paymentOrder);

            $this->payshopUpdateOrder->execute(
                PayshopPaymentMethods::PAYSHOP_REFERENCE,
                $prestashopOrderId,
                'PAYSHOP_ORDER_STATUS_WAITING_PAYSHOP',
                $paymentOrder['uuid']
            );

            Tools::redirect(
                PayshopHelpers::confirmationPageURL($this->module)
            );
        } catch (\Throwable $e) {
            PayshopHelpers::errorResponse($e->getMessage());
        }
    }

    /**
     * Save iframe url in the session to show it in the confirmation page
     * 
     * @param array $paymentOrder
     * @return void
     */
    private function saveIframeContent($paymentOrder)
    {
        $iframeContent = PayshopClientFactory::getInstance()->getIframeContent($paymentOrder['token'] . '?apm=PAYSHOP') ;

        $_SESSION['payshop_iframe_content'] = $iframeContent;
    }
}