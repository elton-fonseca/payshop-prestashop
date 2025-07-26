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

class PayshopProcessEventModuleFrontController extends ModuleFrontController
{
    /**
     * @var PayshopProcessEvent
     */
    private $payshopProcessEvent;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->payshopProcessEvent = new PayshopProcessEvent($this->module);
    }

    /**
     * Process webhook events from the Gateway
     *
     * @return void
     */
    public function postProcess()
    {
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            PayshopEvent::checkEventSignature(
                $data,
                PayshopClientFactory::getSignature()
            );

            if ($this->unprocessablePaymentOrderStatuses($data['order']['status'])) {
                return;
            }

            $this->payshopProcessEvent->execute($data['order']);
        } catch (\Throwable $e) {
            PayshopLog::generate('PayshopProcessEvent: ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'error');

            echo $e->getMessage();

            http_response_code(500);
        }
    }

    /**
     * Check if the status is unprocessable
     *
     * @param string $orderPaymentStatus
     * @return bool
     */
    private function unprocessablePaymentOrderStatuses($orderPaymentStatus)
    {
        return in_array($orderPaymentStatus, [
            'CREATED',
            'PARTIALLY_REFUNDED',
            'PARTIALLY_CONFIRMED',
            'REFUNDED',
            'PENDING_PROCESSOR_RESPONSE',
            'PENDING_3DS_RESPONSE',
            'PENDING_CARD',
            'USER_CANCELLED',
            'REDIRECTED_TO_3DS',
            'AUTHENTICATION_REQUIRED',
            'PENDING_PAYMENT'
        ]);
    }
}
