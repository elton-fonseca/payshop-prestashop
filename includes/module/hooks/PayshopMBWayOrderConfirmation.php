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

class PayshopMBWayOrderConfirmation
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
     * Display (waiting/confirmad/rejected) MBWay dialog on order confirmation page
     *
     * @param Order $order
     * @return string
     */
    public function execute($order)
    {
        $orderCurrentState = $order->getCurrentState();
        $paymentSuccess = false;
        $paymentDeclined = false;

        if ($orderCurrentState == Configuration::get('PAYSHOP_ORDER_STATUS_PAID')) {
            $paymentSuccess = true;
        }

        if ($orderCurrentState == Configuration::get('PAYSHOP_ORDER_STATUS_PAYMENT_ERROR')) {
            $paymentDeclined = true;
        }

        $mBWayPaidCheckUrl = $this->module->context->link->getModuleLink(
            $this->module->name,
            'MBWayPaidCheck'
        );

        $smarty = $this->module->context->smarty;
        $smarty->assign([
            'prestashopOrderId' => $order->id,
            'paymentSuccess' => $paymentSuccess,
            'paymentDeclined' => $paymentDeclined,
            'mBWayPaidCheckUrl' => $mBWayPaidCheckUrl,
            'moduleUrl' => $this->module->path,
        ]);

        return $smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/mbway-dialog.tpl');
    }
}
