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

class PayshopMBWayCreateOrderModuleFrontController extends ModuleFrontController
{
    /**
     * @var CreateOrder
     */
    private $createOrder;

    /**
     * @var SendOrderToPayshop
     */
    private $sendOrderToPayshop;

    /**
     * @var UpdateOrder
     */
    private $updateOrder;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->createOrder = new CreateOrder($this->module);
        $this->sendOrderToPayshop = new SendOrderToPayshop($this->module);
        $this->updateOrder = new UpdateOrder($this->module);
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
            $paymentMethod = 'mbway';

            $instrumentResponse = $this->sendOrderToPayshop->execute(
                $paymentMethod,
                $this->getWBWayPhoneNumber()
            );

            $prestashopOrderId = $this->createOrder->execute($paymentMethod);

            $this->updateOrder->execute(
                $paymentMethod,
                $prestashopOrderId,
                'PAYSHOP_ORDER_STATUS_WAITING_PAYMENT',
                $instrumentResponse['charge']['id'],
                $instrumentResponse['id']
            );

            echo '{
                "status": "success",
                "prestashopOrderId": "' . $prestashopOrderId . '",
                "sucessRedirectUrl": "' . $this->redirectToOrderConfirmationPage() . '"
            }';
        } catch (\Exception $e) {
            echo '{"error":true,"message":"' . $e->getMessage() . '"}';
            http_response_code(400);
        }
    }

    /**
     * Get phone number from the request
     *
     * @return array
     */
    private function getWBWayPhoneNumber()
    {
        if (!Tools::getValue('phone-number')) {
            throw new Exception('Phone number is required');
        }

        $number = str_replace(' ', '', Tools::getValue('phone-number'));

        return [
            'phone' => $number,
        ];
    }

    private function redirectToOrderConfirmationPage()
    {
        $cart = $this->module->context->cart;
        $cartId = (int) $cart->id;
        $orderId = (int) $this->module->currentOrder;

        $customer = new Customer($cart->id_customer);
        $securityKey = $customer->secure_key;

        $moduloId = (int) $this->module->id;

        $link = $this->module->context->link->getBaseLink() . 'index.php?controller=order-confirmation&id_cart='
        . $cartId . '&id_module='
        . $moduloId . '&id_order=' . $orderId . '&key=' . $securityKey;

        return $link;
    }


}
