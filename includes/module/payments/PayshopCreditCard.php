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

class PayshopCreditCard
{
    /**
     * @var Payshop
     */
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Register the credit card payment method
     *
     * @return PrestaShop\PrestaShop\Core\Payment\PaymentOption
     */
    public function register()
    {
        $formAction = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessCard'
        );

                // $paymentForm = $this->module->context->smarty->assign([
        //     'formAction' => $formAction,
        // ])
        //   ->fetch('module:payshop/views/templates/hook/payments/credit-card.tpl');
        $this->module->context->smarty->assign([
            'formAction' => $formAction,
        ]);
        $paymentForm = $this->module->context->smarty->fetch('module:payshop/views/templates/hook/payments/credit-card.tpl');

        $creditCardCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $creditCardCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with credit and debit cards', 'PayshopCreditCard'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/visa_mc.png');

        return $creditCardCheckout;
    }
}
