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

class PayshopClicktopay
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Register the ClickToPay payment method
     *
     * @return PrestaShop\PrestaShop\Core\Payment\PaymentOption
     */
    public function register()
    {
        $formAction = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessClicktopay'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
            'moduleUrl' => $this->module->path,
        ])
          ->fetch('module:payshop/views/templates/hook/payments/clicktopay.tpl');

        $payshopClicktopayCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopClicktopayCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with ClickToPay', 'PayshopClicktopay'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/clicktopay.png');

        return $payshopClicktopayCheckout;
    }
}
