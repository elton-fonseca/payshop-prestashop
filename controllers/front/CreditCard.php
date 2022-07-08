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



class PayshopCreditCardModuleFrontController extends ModuleFrontController
{
    private $createOrder;

    private $sendOrderToPayshop;

    private $updateOrder;

    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->createOrder = new CreateOrder($this->module);
        $this->sendOrderToPayshop = new SendOrderToPayshop($this->module);
        $this->updateOrder = new UpdateOrder($this->module);
    }

    /**
     * @throws Exception
     */
    public function postProcess()
    {
        $cart = $this->context->cart;

        try {
            $paymentMethod = 'card';

            $instrumentResponse = $this->sendOrderToPayshop->execute(
                $paymentMethod,
                $this->getCardData()
            );

            $prestashopOrderId = $this->createOrder->execute($paymentMethod);

            $this->updateOrder->execute(
                $prestashopOrderId,
                $this->getOrderStatus($instrumentResponse),
                $instrumentResponse['charge']['id'],
                $instrumentResponse['id'],
                $this->paymentId($instrumentResponse)
            );

            $this->redirectToOrderConfirmationPage();
        } catch (\Exception $e) {
            Tools::redirect('index.php?controller=order&step=3&typeReturn=failure');
        }

    }

    private function getCardData()
    {
        $this->validateCardData();

        $cardExpiration = Tools::getValue('card-expiration');

        return [
            'number' => Tools::getValue('card-number'),
            'expiration_month' => substr($cardExpiration, 0, 2),
            'expiration_year' => substr($cardExpiration, 3, 4),
            'cvc' => Tools::getValue('card-security-code'),
            'name' => Tools::getValue('card-name')
        ];
    }

    private function validateCardData()
    {
        if (!Tools::getValue('card-number')) {
            throw new Exception('Card number is required');
        }

        if (strlen(Tools::getValue('card-number')) < 15) {
            throw new Exception('Card number is invalid');
        }

        if (!Tools::getValue('card-expiration')) {
            throw new Exception('Card expiration is required');
        }

        if (strlen(Tools::getValue('card-expiration')) < 7) {
            throw new Exception('Card expiration is invalid');
        }

        if (!Tools::getValue('card-security-code')) {
            throw new Exception('Card security code is required');
        }

        if (strlen(Tools::getValue('card-security-code')) < 3) {
            throw new Exception('Card security code is invalid');
        }

        if (!Tools::getValue('card-holder-name')) {
            throw new Exception('Card holder name is required');
        }
    }

    private function paymentId($instrumentResponse)
    {
        if (isset($instrumentResponse['last_payment'])) {
            return $instrumentResponse['last_payment']['id'];
        }
        
        return null;
    }

    public function getOrderStatus($instrumentResponse)
    {
        $issetPayment = isset($instrumentResponse['last_payment']);
        $paymentSucess = $issetPayment && $instrumentResponse['last_payment']['success'];
        $paymentStatusSucess = $issetPayment && $instrumentResponse['last_payment']['status'] == 'success';

        if ($paymentSucess && $paymentStatusSucess) {
            return 'PAYSHOP_ORDER_STATUS_PAID';
        }

        return 'PAYSHOP_ORDER_STATUS_PAYMENT_ERROR';
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
        . $moduloId. '&id_order=' . $orderId . '&key=' . $securityKey;

        Tools::redirect($link);
    }
}
