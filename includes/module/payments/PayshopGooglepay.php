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

class PayshopGooglepay
{
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Register the Googlepay payment method
     *
     * @return PaymentOption
     */
    public function register()
    {
        $formAction = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessGooglepay'
        );

        $paymentForm = $this->module->context->smarty->assign([
            'formAction' => $formAction,
            'moduleUrl' => $this->module->path,
        ])
          ->fetch('module:payshop/views/templates/hook/payments/googlepay.tpl');

        $payshopGooglepayCheckout = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $payshopGooglepayCheckout->setForm($paymentForm)
            ->setCallToActionText($this->module->l('Pay with Google Pay', 'PayshopGooglepay'))
            ->setLogo(_MODULE_DIR_ . 'payshop/views/img/googlepay.png');

        return $payshopGooglepayCheckout;
    }
}
