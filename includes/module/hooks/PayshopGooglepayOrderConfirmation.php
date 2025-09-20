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
class PayshopGooglepayOrderConfirmation
{
    /**
     * @var Module
     */
    private $module;

    /**
     * Class constructor
     *
     * @param Module $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Display Google Pay dialog on the order confirmation page
     *
     * @param Order $order
     *
     * @return string|void|null
     */
    public function execute($order)
    {
        $sessionKey = '_payshop_googlepay_paid_' . $order->id;

        if (isset($_SESSION[$sessionKey]) && $_SESSION[$sessionKey]) {
            return;
        }

        $processWalletPayment = Context::getContext()->link->getModuleLink(
            $this->module->name,
            'ProcessWalletPayment'
        );

        $processFailedRedirect = Context::getContext()->link->getModuleLink(
            $this->module->name,
            'ProcessFailedRedirect',
            ['prestashop_order_id' => $order->id]
        );

        $smarty = Context::getContext()->smarty;
        $smarty->assign([
            'moduleUrl' => 'modules/' . $this->module->name,
            'storeName' => Configuration::get('PS_SHOP_NAME'),
            'environment' => PayshopClientFactory::isProduction() ? 'PRODUCTION' : 'TEST',
            'googlePayMerchantId' => Configuration::get('PAYSHOP_GOOGLEPAY_MERCHANT_ID'),
            'total' => number_format($order->total_paid, 2, '.', ''),
            'processWalletPayment' => $processWalletPayment,
            'processFailedRedirect' => $processFailedRedirect,
            'orderId' => $order->id,
        ]);

        return $smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/googlepay-dialog.tpl');
    }
}
