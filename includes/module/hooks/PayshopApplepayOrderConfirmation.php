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

class PayshopApplepayOrderConfirmation
{
    /**
     * @var Modulo
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
     * @return string
     */
    public function execute($order)
    {
        $applepayMerchantValidation = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ApplepayMerchantValidation'
        );

        $processWalletPayment = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessWalletPayment'
        );

        $processFailedRedirect = $this->module->context->link->getModuleLink(
            $this->module->name,
            'ProcessFailedRedirect',
            ['prestashop_order_id' => $order->id]
        );

        $smarty = $this->module->context->smarty;
        $smarty->assign([
            'moduleUrl' => $this->module->path,
            'storeName' => Configuration::get('PS_SHOP_NAME'),
            'applepayMerchantValidation' => $applepayMerchantValidation,
            'processWalletPayment' => $processWalletPayment,
            'processFailedRedirect' => $processFailedRedirect,
            'total' => number_format($order->total_paid, 2, '.', ''),
            'orderId' => $order->id,
        ]);

        return $smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/applepay-dialog.tpl');
    }
}
