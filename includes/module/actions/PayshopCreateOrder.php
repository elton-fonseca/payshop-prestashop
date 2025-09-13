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
class PayshopCreateOrder
{
    /**
     * @var PaymentModule
     */
    private $module;

    /**
     * @var PayshopCreatePaymentOrder
     */
    private $payshopCreatePaymentOrder;

    /**
     * @var PayshopCreatePrestashopOrder
     */
    private $payshopCreatePrestashopOrder;

    /**
     * @var PayshopUpdateOrder
     */
    private $payshopUpdateOrder;

    /**
     * Class constructor
     *
     * @param PaymentModule $module
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->payshopCreatePaymentOrder = new PayshopCreatePaymentOrder($module);
        $this->payshopCreatePrestashopOrder = new PayshopCreatePrestashopOrder($module);
        $this->payshopUpdateOrder = new PayshopUpdateOrder($module);
    }

    /**
     * Prepare the payment order and Prestashop Order to all payments
     *
     * @param string $paymentMethod
     *
     * @return array
     */
    public function execute($paymentMethod)
    {
        $this->checkoutIsFilled();
        $this->moduleIsAuthorized();
        $prestashopOrderId = $this->payshopCreatePrestashopOrder->execute($paymentMethod);

        $paymentOrder = $this->payshopCreatePaymentOrder->execute(
            $paymentMethod, $prestashopOrderId
        );

        $this->payshopUpdateOrder->execute(
            $paymentMethod,
            $prestashopOrderId,
            'PAYSHOP_ORDER_STATUS_WAITING_PAYMENT',
            $paymentOrder['uuid']
        );

        return [$prestashopOrderId, $paymentOrder];
    }

    /**
     * Checkout if all informations are filled on checkout page
     *
     * return void
     */
    private function checkoutIsFilled()
    {
        // $cart = $this->module->getContext()->cart;
        $cart = $this->module->context->cart;

        $moduleDisabed = !$this->module->active;
        $cartIsEmpty = !$cart->id;
        $clientNotFilled = $cart->id_customer == 0;
        $deliveryAddressNotFilled = $cart->id_address_delivery == 0;
        $invoiceAddressNotFilled = $cart->id_address_invoice == 0;

        if (
            $moduleDisabed || $cartIsEmpty || $clientNotFilled
            || $deliveryAddressNotFilled || $invoiceAddressNotFilled
        ) {
            throw new Exception($this->module->l('Checkout fields are not filled', 'PayshopCreateCharge'));
        }
    }

    /**
     * Check if module is authorized
     *
     * return void
     *
     * @throws Exception
     */
    private function moduleIsAuthorized()
    {
        $authorized = false;

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'payshop') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            throw new Exception($this->module->l('This payment method is not available.', 'PayshopCreateCharge'));
        }
    }
}
